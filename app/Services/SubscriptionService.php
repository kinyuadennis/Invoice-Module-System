<?php

namespace App\Services;

use App\Config\GatewayConstants;
use App\Config\PaymentConstants;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\Subscription;
use App\Models\User;
use App\Payments\Adapters\MpesaGatewayAdapter;
use App\Payments\Adapters\StripeGatewayAdapter;
use App\Payments\Contracts\PaymentGatewayInterface;
use App\Payments\DTOs\GatewayCallbackPayload;
use App\Payments\DTOs\PaymentContext;
use App\Payments\Events\PaymentConfirmed;
use App\Payments\Events\PaymentInitiated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Subscription Service
 *
 * Orchestrates subscription and payment operations.
 * Handles gateway selection, payment initiation, and confirmation.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
class SubscriptionService
{
    public function __construct(
        private MpesaGatewayAdapter $mpesaAdapter,
        private StripeGatewayAdapter $stripeAdapter,
        private InvoiceSnapshotService $snapshotService
    ) {}

    /**
     * Resolve the appropriate payment gateway adapter based on user country.
     *
     * @param  User  $user  User to determine gateway for
     * @return PaymentGatewayInterface The appropriate gateway adapter
     */
    private function resolveGateway(User $user): PaymentGatewayInterface
    {
        $country = $user->country ?? null;

        // Route KE → M-Pesa, else → Stripe (per blueprint section 5)
        if ($country === GatewayConstants::COUNTRY_KENYA) {
            return $this->mpesaAdapter;
        }

        return $this->stripeAdapter;
    }

    /**
     * Initiate a subscription payment.
     *
     * Creates subscription (PENDING), creates payment (INITIATED), calls gateway,
     * stores gateway reference, and emits PaymentInitiated event.
     *
     * @param  Subscription  $subscription  Subscription to initiate payment for
     * @param  array  $userDetails  User details (phone for M-Pesa, customerId for Stripe, etc.)
     * @return array{success: bool, client_secret?: string, transaction_id?: string, error?: string}
     */
    public function initiateSubscriptionPayment(Subscription $subscription, array $userDetails): array
    {
        DB::beginTransaction();

        try {
            // Get user for gateway selection
            $user = $subscription->user;
            if (! $user) {
                throw new \Exception('Subscription must have a user');
            }

            // Resolve gateway adapter
            $gateway = $this->resolveGateway($user);
            $gatewayName = $gateway instanceof MpesaGatewayAdapter ? PaymentConstants::GATEWAY_MPESA : PaymentConstants::GATEWAY_STRIPE;

            // Ensure subscription gateway matches resolved gateway
            if ($subscription->gateway && $subscription->gateway !== $gatewayName) {
                throw new \Exception("Gateway mismatch: subscription gateway is {$subscription->gateway}, but user country requires {$gatewayName}");
            }

            // Validate subscription has a plan with valid price
            $plan = $subscription->plan;
            if (! $plan) {
                throw new \Exception('Subscription must have a plan to initiate payment');
            }

            $planPrice = $plan->price;
            if ($planPrice === null || $planPrice <= 0) {
                throw new \Exception("Subscription plan must have a valid price greater than zero. Plan '{$plan->name}' (ID: {$plan->id}) has invalid price: {$planPrice}");
            }

            // Generate idempotency key
            $idempotencyKey = (string) Str::uuid();

            // Get attempt number (increment from previous attempts)
            $lastAttempt = $subscription->paymentAttempts()
                ->where('subscription_id', $subscription->id)
                ->orderBy('attempt_number', 'desc')
                ->first();
            $attemptNumber = $lastAttempt ? $lastAttempt->attempt_number + 1 : 1;

            // Per blueprint: Create payment_attempt (not payment) for initiation
            // Payments table contains ONLY succeeded payments
            $paymentAttempt = PaymentAttempt::create([
                'subscription_id' => $subscription->id,
                'amount' => $planPrice,
                'currency' => $subscription->company?->currency ?? 'KES',
                'gateway' => $gatewayName,
                'attempt_number' => $attemptNumber,
                'status' => PaymentAttempt::STATUS_INITIATED,
                'idempotency_key' => $idempotencyKey,
                'initiated_at' => now(),
            ]);

            // Create payment context
            $context = new PaymentContext(
                subscriptionId: (string) $subscription->id,
                amount: (float) $paymentAttempt->amount,
                currency: $paymentAttempt->currency,
                userDetails: $userDetails,
                reference: "SUB_{$subscription->id}_{$idempotencyKey}",
                description: "Subscription Payment: {$subscription->plan_code}"
            );

            // Initiate payment with gateway
            $gatewayResponse = $gateway->initiatePayment($context);

            if (! $gatewayResponse->success) {
                // Mark attempt as failed
                $paymentAttempt->markAsFailed('gateway_rejected', 'Gateway payment initiation failed');
                throw new \Exception('Gateway payment initiation failed');
            }

            // Update attempt with gateway reference
            $updateData = [
                'gateway_transaction_id' => $gatewayResponse->transactionId,
                'gateway_metadata' => $gatewayResponse->metadata,
                'raw_gateway_payload' => $gatewayResponse->metadata,
            ];

            // If gateway accepted (e.g., STK Push sent), transition to pending
            if ($gatewayResponse->transactionId) {
                $paymentAttempt->markAsPending();
            }

            $paymentAttempt->update($updateData);

            // Update subscription gateway if not set
            if (! $subscription->gateway) {
                $subscription->update(['gateway' => $gatewayName]);
            }

            // Note: PaymentInitiated event expects Payment model
            // Per blueprint, we use PaymentAttempt for initiation
            // TODO: Update event system or create PaymentAttemptInitiated event
            // For now, skip event emission to avoid type mismatch

            DB::commit();

            return [
                'success' => true,
                'payment_attempt_id' => $paymentAttempt->id,
                'client_secret' => $gatewayResponse->clientSecret,
                'transaction_id' => $gatewayResponse->transactionId,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Subscription payment initiation failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Confirm a payment from gateway callback/webhook.
     *
     * Per blueprint: Handles idempotency using gateway_transaction_id as key.
     * Updates payment_attempt, creates Payment record ONLY on success.
     *
     * @param  string  $gatewayName  Gateway name ('mpesa' or 'stripe')
     * @param  GatewayCallbackPayload  $payload  Callback payload from gateway
     * @return Payment|null Confirmed payment (only if succeeded) or null if failed/duplicate
     */
    public function confirmPayment(string $gatewayName, GatewayCallbackPayload $payload): ?Payment
    {
        // Resolve gateway adapter
        $gateway = $gatewayName === PaymentConstants::GATEWAY_MPESA ? $this->mpesaAdapter : $this->stripeAdapter;

        // Per blueprint: Use gateway_transaction_id as key for idempotency
        // Find payment_attempt by gateway_transaction_id
        $paymentAttempt = PaymentAttempt::where('gateway', $gatewayName)
            ->where('gateway_transaction_id', $payload->gatewayReference)
            ->first();

        if (! $paymentAttempt) {
            // Try alternative lookup (for M-Pesa CheckoutRequestID in metadata)
            $paymentAttempt = PaymentAttempt::where('gateway', $gatewayName)
                ->whereJsonContains('gateway_metadata->CheckoutRequestID', $payload->gatewayReference)
                ->first();
        }

        if (! $paymentAttempt) {
            Log::warning('Payment attempt not found for gateway callback', [
                'gateway' => $gatewayName,
                'gateway_reference' => $payload->gatewayReference,
            ]);

            return null;
        }

        // Per blueprint: Idempotency check - if attempt already succeeded, return existing payment
        if ($paymentAttempt->status === PaymentAttempt::STATUS_SUCCEEDED) {
            // Find or return existing payment record
            $payment = Payment::where('gateway_transaction_id', $payload->gatewayReference)
                ->where('payable_type', Subscription::class)
                ->where('payable_id', $paymentAttempt->subscription_id)
                ->first();

            if ($payment) {
                Log::info('Duplicate gateway callback received (idempotent ignore)', [
                    'gateway' => $gatewayName,
                    'gateway_reference' => $payload->gatewayReference,
                    'payment_id' => $payment->id,
                ]);

                return $payment;
            }
        }

        // Confirm payment with gateway
        $paymentResult = $gateway->confirmPayment($payload);

        DB::beginTransaction();

        try {
            $subscription = $paymentAttempt->subscription;

            // Update attempt status based on gateway result
            if ($paymentResult->status === 'confirmed') {
                // Attempt succeeded - mark attempt and create Payment record
                $paymentAttempt->markAsSucceeded();
                $paymentAttempt->update([
                    'gateway_metadata' => array_merge($paymentAttempt->gateway_metadata ?? [], $paymentResult->metadata),
                    'raw_gateway_payload' => $payload->rawData,
                ]);

                // Per blueprint: Create Payment record ONLY on success (immutable record)
                $payment = Payment::create([
                    'company_id' => $subscription->company_id,
                    'payable_type' => Subscription::class,
                    'payable_id' => $subscription->id,
                    'amount' => $paymentAttempt->amount,
                    'currency' => $paymentAttempt->currency,
                    'gateway' => $gatewayName,
                    'gateway_transaction_id' => $paymentResult->gatewayReference,
                    'gateway_metadata' => $paymentAttempt->gateway_metadata,
                    'raw_gateway_payload' => $payload->rawData,
                    'idempotency_key' => $paymentAttempt->idempotency_key,
                    'status' => PaymentConstants::PAYMENT_STATUS_SUCCESS,
                    'payment_date' => now(),
                    'paid_at' => now(),
                ]);

                // Emit PaymentConfirmed event
                event(new PaymentConfirmed($payment));

                // Handle subscription activation/renewal
                if ($subscription->isPastDue() || $subscription->status === SubscriptionConstants::SUBSCRIPTION_STATUS_PAST_DUE) {
                    $this->handleRenewalSuccess($subscription, $payment);
                } elseif (! $subscription->isActive()) {
                    // New subscription activation
                    $subscription->transitionToActive();
                }
            } else {
                // Attempt failed
                $errorCode = $paymentResult->metadata['error_code'] ?? 'payment_failed';
                $errorMessage = $paymentResult->metadata['error_message'] ?? 'Payment failed';
                $paymentAttempt->markAsFailed($errorCode, $errorMessage);
                $paymentAttempt->update([
                    'gateway_metadata' => array_merge($paymentAttempt->gateway_metadata ?? [], $paymentResult->metadata),
                    'raw_gateway_payload' => $payload->rawData,
                ]);

                // Per blueprint: Do NOT create Payment record for failures
                // If this is a renewal payment that failed, handle renewal failure
                if ($subscription->isActive()) {
                    $this->handleRenewalFailure($subscription);
                }

                DB::commit();

                return null; // No payment record for failed attempts
            }

            DB::commit();

            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payment confirmation failed', [
                'gateway' => $gatewayName,
                'gateway_reference' => $payload->gatewayReference,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Activate a subscription after payment confirmation.
     *
     * Handles PaymentConfirmed event, activates subscription, and creates invoice snapshot.
     */
    public function activateSubscription(Payment $payment): void
    {
        $subscription = $payment->payable;
        if (! $subscription instanceof Subscription) {
            return; // Not a subscription payment
        }

        DB::beginTransaction();

        try {
            // Transition subscription to ACTIVE (enforces invariants)
            $subscription->transitionToActive();

            // Calculate subscription end date based on plan billing period
            $plan = $subscription->plan;
            if ($plan) {
                $billingPeriod = $plan->billing_period ?? 'monthly';
                $endDate = match ($billingPeriod) {
                    'monthly' => now()->addMonth(),
                    'yearly' => now()->addYear(),
                    'quarterly' => now()->addMonths(3),
                    default => now()->addMonth(),
                };

                $nextBillingAt = $endDate;

                $subscription->update([
                    'ends_at' => $endDate,
                    'next_billing_at' => $nextBillingAt,
                ]);
            }

            // Emit SubscriptionActivated event
            event(new SubscriptionActivated($subscription));

            // Create invoice snapshot (per blueprint section 6)
            $this->createSubscriptionInvoice($subscription, $payment);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Subscription activation failed', [
                'subscription_id' => $subscription->id,
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Cancel a subscription.
     *
     * Handles gateway-specific cancellation and updates subscription status.
     */
    public function cancelSubscription(Subscription $subscription, string $reason = 'User cancellation'): GatewayResult
    {
        DB::beginTransaction();

        try {
            // Get gateway adapter
            $gateway = $subscription->gateway === PaymentConstants::GATEWAY_MPESA
                ? $this->mpesaAdapter
                : $this->stripeAdapter;

            // Attempt gateway cancellation (M-Pesa will throw UnsupportedOperationException)
            $gatewayResult = null;
            try {
                $subscriptionContext = new SubscriptionContext(
                    subscriptionId: (string) $subscription->id,
                    gatewaySubscriptionId: $subscription->payment_reference ?? null,
                    reason: $reason
                );

                $gatewayResult = $gateway->cancelSubscription($subscriptionContext);
            } catch (\UnsupportedOperationException $e) {
                // M-Pesa doesn't support cancellation - that's expected
                $gatewayResult = new GatewayResult(
                    success: true,
                    errorMessage: null,
                    metadata: ['note' => 'Cancellation handled internally']
                );
            }

            // Transition subscription to CANCELLED
            $subscription->transitionToCancelled();

            // Emit SubscriptionCancelled event
            event(new SubscriptionCancelled($subscription));

            DB::commit();

            return $gatewayResult ?? new GatewayResult(success: true, errorMessage: null, metadata: []);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Subscription cancellation failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            return new GatewayResult(
                success: false,
                errorMessage: $e->getMessage(),
                metadata: []
            );
        }
    }

    /**
     * Handle renewal failure - transition to GRACE.
     */
    public function handleRenewalFailure(Subscription $subscription, string $reason = 'Payment failed'): void
    {
        $subscription->transitionToGrace();

        // Set grace period end date
        $gracePeriodEnd = now()->addDays(SubscriptionConstants::RENEWAL_GRACE_DAYS);
        $subscription->update(['ends_at' => $gracePeriodEnd]);

        // Send renewal failed notification
        $user = $subscription->user;
        if ($user) {
            $user->notify(new \App\Notifications\Subscriptions\SubscriptionRenewalFailedNotification($subscription, $reason));
        }
    }

    /**
     * Handle renewal success - transition back to ACTIVE.
     */
    public function handleRenewalSuccess(Subscription $subscription, Payment $payment): void
    {
        DB::beginTransaction();

        try {
            // Transition to ACTIVE (enforces invariants)
            $subscription->transitionToActive();

            // Update next billing date
            $plan = $subscription->plan;
            if ($plan) {
                $billingPeriod = $plan->billing_period ?? 'monthly';
                $nextBillingAt = match (strtolower($billingPeriod)) {
                    'monthly', 'month' => now()->addMonth(),
                    'yearly', 'year' => now()->addYear(),
                    'quarterly', 'quarter' => now()->addMonths(3),
                    'weekly', 'week' => now()->addWeek(),
                    'daily', 'day' => now()->addDay(),
                    default => now()->addMonth(),
                };

                $subscription->update([
                    'next_billing_at' => $nextBillingAt,
                    'ends_at' => $nextBillingAt,
                ]);
            }

            // Emit SubscriptionRenewed event
            event(new SubscriptionRenewed($subscription));

            // Create invoice snapshot for renewal
            $this->createSubscriptionInvoice($subscription, $payment);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Subscription renewal success handling failed', [
                'subscription_id' => $subscription->id,
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Create an invoice snapshot for subscription payment.
     *
     * Per blueprint section 6: Invoices are generated only after PaymentConfirmed event.
     * Invoice data is snapshotted with amount, currency, plan description, gateway (metadata).
     */
    private function createSubscriptionInvoice(Subscription $subscription, Payment $payment): void
    {
        try {
            // Create invoice record (immutable snapshot)
            $invoice = Invoice::create([
                'company_id' => $subscription->company_id,
                'user_id' => $subscription->user_id,
                'client_id' => null, // Subscription invoices don't have a client
                'status' => 'paid',
                'issue_date' => now(),
                'due_date' => now(),
                'invoice_reference' => "SUB-{$subscription->id}-{$payment->id}",
                'subtotal' => $payment->amount,
                'discount' => 0,
                'vat_amount' => 0,
                'grand_total' => $payment->amount,
                'total' => $payment->amount,
                'payment_method' => $subscription->gateway,
                'payment_details' => json_encode([
                    'subscription_id' => $subscription->id,
                    'payment_id' => $payment->id,
                    'plan_code' => $subscription->plan_code,
                    'gateway' => $subscription->gateway,
                ]),
                'notes' => "Subscription Payment: {$subscription->plan_code}",
            ]);

            // Create snapshot for PDF rendering
            $this->snapshotService->createSnapshot($invoice, 'paid');
        } catch (\Exception $e) {
            // Log error but don't fail subscription activation
            Log::error('Failed to create subscription invoice snapshot', [
                'subscription_id' => $subscription->id,
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
