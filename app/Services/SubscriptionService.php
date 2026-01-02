<?php

namespace App\Services;

use App\Config\GatewayConstants;
use App\Config\PaymentConstants;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Payments\Adapters\MpesaGatewayAdapter;
use App\Payments\Adapters\StripeGatewayAdapter;
use App\Payments\Contracts\PaymentGatewayInterface;
use App\Payments\DTOs\GatewayCallbackPayload;
use App\Payments\DTOs\PaymentContext;
use App\Payments\Events\PaymentConfirmed;
use App\Payments\Events\PaymentFailed;
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

            // Generate idempotency key
            $idempotencyKey = (string) Str::uuid();

            // Create payment record (INITIATED status)
            $payment = Payment::create([
                'company_id' => $subscription->company_id,
                'payable_type' => Subscription::class,
                'payable_id' => $subscription->id,
                'amount' => $subscription->plan?->price ?? 0, // TODO: Get amount from plan
                'gateway' => $gatewayName,
                'status' => PaymentConstants::PAYMENT_STATUS_INITIATED,
                'idempotency_key' => $idempotencyKey,
                'payment_date' => now(),
            ]);

            // Create payment context
            $context = new PaymentContext(
                subscriptionId: (string) $subscription->id,
                amount: (float) $payment->amount,
                currency: $subscription->company?->currency ?? 'KES',
                userDetails: $userDetails,
                reference: "SUB_{$subscription->id}_{$idempotencyKey}",
                description: "Subscription Payment: {$subscription->plan_code}"
            );

            // Initiate payment with gateway
            $gatewayResponse = $gateway->initiatePayment($context);

            if (! $gatewayResponse->success) {
                throw new \Exception('Gateway payment initiation failed');
            }

            // Update payment with gateway reference
            $payment->update([
                'gateway_transaction_id' => $gatewayResponse->transactionId,
                'gateway_metadata' => $gatewayResponse->metadata,
                'raw_gateway_payload' => $gatewayResponse->metadata,
            ]);

            // Update subscription gateway if not set
            if (! $subscription->gateway) {
                $subscription->update(['gateway' => $gatewayName]);
            }

            // Emit PaymentInitiated event
            event(new PaymentInitiated($payment));

            DB::commit();

            return [
                'success' => true,
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
     * Handles idempotency, updates payment status, and emits appropriate events.
     *
     * @param  string  $gatewayName  Gateway name ('mpesa' or 'stripe')
     * @param  GatewayCallbackPayload  $payload  Callback payload from gateway
     * @return Payment|null Confirmed payment or null if failed/duplicate
     */
    public function confirmPayment(string $gatewayName, GatewayCallbackPayload $payload): ?Payment
    {
        // Resolve gateway adapter
        $gateway = $gatewayName === PaymentConstants::GATEWAY_MPESA ? $this->mpesaAdapter : $this->stripeAdapter;

        // Check for existing payment by gateway_reference (idempotency)
        $existingPayment = Payment::where('gateway', $gatewayName)
            ->where('gateway_transaction_id', $payload->gatewayReference)
            ->first();

        if ($existingPayment && in_array($existingPayment->status, [
            PaymentConstants::PAYMENT_STATUS_SUCCESS,
            PaymentConstants::PAYMENT_STATUS_FAILED,
            PaymentConstants::PAYMENT_STATUS_TIMEOUT,
        ])) {
            // Duplicate callback - already processed (idempotent)
            Log::info('Duplicate gateway callback received (idempotent ignore)', [
                'gateway' => $gatewayName,
                'gateway_reference' => $payload->gatewayReference,
                'payment_id' => $existingPayment->id,
            ]);

            return $existingPayment;
        }

        // Confirm payment with gateway
        $paymentResult = $gateway->confirmPayment($payload);

        DB::beginTransaction();

        try {
            // Find payment record
            if ($existingPayment) {
                $payment = $existingPayment;
            } else {
                // Try to find by gateway reference in metadata (for M-Pesa CheckoutRequestID or Stripe PaymentIntent ID)
                $payment = Payment::where('gateway', $gatewayName)
                    ->where(function ($query) use ($payload) {
                        $query->whereJsonContains('gateway_metadata->CheckoutRequestID', $payload->gatewayReference)
                            ->orWhere('gateway_transaction_id', $payload->gatewayReference);
                    })
                    ->first();

                if (! $payment) {
                    Log::warning('Payment not found for gateway callback', [
                        'gateway' => $gatewayName,
                        'gateway_reference' => $payload->gatewayReference,
                    ]);

                    DB::rollBack();

                    return null;
                }
            }

            // Update payment status
            $paymentStatus = match ($paymentResult->status) {
                'confirmed' => PaymentConstants::PAYMENT_STATUS_SUCCESS,
                'failed' => PaymentConstants::PAYMENT_STATUS_FAILED,
                'timeout' => PaymentConstants::PAYMENT_STATUS_TIMEOUT,
                default => PaymentConstants::PAYMENT_STATUS_FAILED,
            };

            $payment->update([
                'status' => $paymentStatus,
                'gateway_transaction_id' => $paymentResult->gatewayReference,
                'gateway_metadata' => array_merge($payment->gateway_metadata ?? [], $paymentResult->metadata),
                'raw_gateway_payload' => $payload->rawData,
                'paid_at' => $paymentStatus === PaymentConstants::PAYMENT_STATUS_SUCCESS ? now() : null,
            ]);

            // Emit appropriate event
            if ($paymentStatus === PaymentConstants::PAYMENT_STATUS_SUCCESS) {
                event(new PaymentConfirmed($payment));
                // Note: Subscription activation is handled by CreateInvoiceOnPaymentConfirmed listener

                // If this is a renewal payment (subscription in GRACE), handle renewal success
                if ($payment->payable_type === Subscription::class) {
                    $subscription = $payment->payable;
                    if ($subscription instanceof Subscription && $subscription->isInGrace()) {
                        $this->handleRenewalSuccess($subscription, $payment);
                    }
                }
            } else {
                event(new PaymentFailed($payment));

                // If this is a renewal payment that failed, handle renewal failure
                if ($payment->payable_type === Subscription::class) {
                    $subscription = $payment->payable;
                    if ($subscription instanceof Subscription && $subscription->isActive()) {
                        $this->handleRenewalFailure($subscription);
                    }
                }
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
