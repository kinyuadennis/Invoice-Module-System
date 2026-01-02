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
        private StripeGatewayAdapter $stripeAdapter
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
                'amount' => $subscription->plan->price ?? 0, // TODO: Get amount from plan
                'gateway' => $gatewayName,
                'status' => PaymentConstants::PAYMENT_STATUS_INITIATED,
                'idempotency_key' => $idempotencyKey,
                'payment_date' => now(),
            ]);

            // Create payment context
            $context = new PaymentContext(
                subscriptionId: (string) $subscription->id,
                amount: (float) $payment->amount,
                currency: $subscription->company->currency ?? 'KES',
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
            } else {
                event(new PaymentFailed($payment));
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
}
