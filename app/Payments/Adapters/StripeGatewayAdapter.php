<?php

namespace App\Payments\Adapters;

use App\Payments\Contracts\PaymentGatewayInterface;
use App\Payments\DTOs\GatewayCallbackPayload;
use App\Payments\DTOs\GatewayResponse;
use App\Payments\DTOs\GatewayResult;
use App\Payments\DTOs\PaymentContext;
use App\Payments\DTOs\PaymentResult;
use App\Payments\DTOs\SubscriptionContext;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Stripe Gateway Adapter
 *
 * Implements PaymentGatewayInterface for Stripe API.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 5.2
 *
 * Stripe-specific behavior:
 * - Automated, provider-driven renewals (native recurring support)
 * - PaymentIntent for one-time payments
 * - Webhook-based confirmation
 * - Native subscription cancellation via Stripe API
 */
class StripeGatewayAdapter implements PaymentGatewayInterface
{
    /**
     * Initiate a payment request with Stripe PaymentIntent.
     *
     * @param  PaymentContext  $context  Payment context
     * @return GatewayResponse Response with PaymentIntent ID, client_secret, and metadata
     *
     * @throws \Exception If PaymentIntent creation fails
     */
    public function initiatePayment(PaymentContext $context): GatewayResponse
    {
        $stripeSecret = config('services.stripe.secret');

        if (! $stripeSecret) {
            throw new \Exception('Stripe credentials not configured. Please configure STRIPE_SECRET in your .env file.');
        }

        try {
            // Create Stripe PaymentIntent
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$stripeSecret,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post('https://api.stripe.com/v1/payment_intents', [
                'amount' => (int) ($context->amount * 100), // Convert to cents
                'currency' => strtolower($context->currency),
                'metadata' => [
                    'subscription_id' => $context->subscriptionId,
                    'reference' => $context->reference,
                ],
                'description' => $context->description,
            ]);

            if (! $response->successful()) {
                throw new \Exception('Stripe API error: '.$response->body());
            }

            $paymentIntent = $response->json();

            return new GatewayResponse(
                transactionId: $paymentIntent['id'],
                clientSecret: $paymentIntent['client_secret'],
                success: true,
                metadata: $paymentIntent
            );
        } catch (\Exception $e) {
            Log::error('Stripe payment initiation failed', [
                'subscription_id' => $context->subscriptionId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Confirm a payment from Stripe webhook.
     *
     * Note: This method parses the webhook and returns PaymentResult.
     * It does NOT create or update Payment records - that is the responsibility
     * of the SubscriptionService (per blueprint: gateways never mutate domain models).
     *
     * @param  GatewayCallbackPayload  $payload  Webhook payload from Stripe
     * @return PaymentResult Result with payment status and metadata
     *
     * @throws \Exception If payment confirmation fails
     */
    public function confirmPayment(GatewayCallbackPayload $payload): PaymentResult
    {
        $eventType = $payload->rawData['type'] ?? null;
        $paymentIntent = $payload->rawData['data']['object'] ?? null;

        if (! $eventType || ! $paymentIntent) {
            throw new \Exception('Stripe webhook missing required fields: type or data.object');
        }

        // Only process payment_intent.succeeded for one-time payments
        if ($eventType !== 'payment_intent.succeeded') {
            throw new \Exception("Unsupported Stripe event type: {$eventType}");
        }

        $paymentIntentId = $paymentIntent['id'] ?? null;
        if (! $paymentIntentId) {
            throw new \Exception('Stripe webhook missing PaymentIntent ID');
        }

        // Determine payment status based on PaymentIntent status
        $status = 'confirmed'; // payment_intent.succeeded means confirmed
        $gatewayReference = $paymentIntentId;

        // Note: paymentId will be set by SubscriptionService after Payment record is created/found
        return new PaymentResult(
            status: $status,
            paymentId: '', // Will be set by service layer
            gatewayReference: $gatewayReference,
            metadata: array_merge($paymentIntent, [
                'event_type' => $eventType,
                'event_id' => $payload->rawData['id'] ?? null,
            ])
        );
    }

    /**
     * Cancel a subscription with Stripe.
     *
     * @param  SubscriptionContext  $context  Subscription context
     * @return GatewayResult Result indicating success or failure
     *
     * @throws \Exception If cancellation fails
     */
    public function cancelSubscription(SubscriptionContext $context): GatewayResult
    {
        $stripeSecret = config('services.stripe.secret');

        if (! $stripeSecret) {
            throw new \Exception('Stripe credentials not configured. Please configure STRIPE_SECRET in your .env file.');
        }

        if (! $context->gatewaySubscriptionId) {
            throw new \Exception('Stripe subscription ID is required for cancellation');
        }

        try {
            // Cancel Stripe subscription (cancel at period end to allow access until period ends)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$stripeSecret,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post("https://api.stripe.com/v1/subscriptions/{$context->gatewaySubscriptionId}", [
                'cancel_at_period_end' => 'true',
            ]);

            if (! $response->successful()) {
                $error = $response->json();
                throw new \Exception('Stripe cancellation failed: '.($error['error']['message'] ?? $response->body()));
            }

            $subscription = $response->json();

            return new GatewayResult(
                success: true,
                errorMessage: null,
                metadata: [
                    'subscription_id' => $subscription['id'],
                    'cancel_at_period_end' => $subscription['cancel_at_period_end'] ?? true,
                    'current_period_end' => $subscription['current_period_end'] ?? null,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Stripe subscription cancellation failed', [
                'subscription_id' => $context->subscriptionId,
                'gateway_subscription_id' => $context->gatewaySubscriptionId,
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
     * Check if Stripe supports recurring payments.
     *
     * @return bool Always returns true - Stripe has native recurring support
     */
    public function supportsRecurring(): bool
    {
        return true;
    }
}
