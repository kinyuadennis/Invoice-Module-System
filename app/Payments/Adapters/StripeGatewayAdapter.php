<?php

namespace App\Payments\Adapters;

use App\Payments\Contracts\PaymentGatewayInterface;
use App\Payments\DTOs\GatewayCallbackPayload;
use App\Payments\DTOs\GatewayResponse;
use App\Payments\DTOs\GatewayResult;
use App\Payments\DTOs\PaymentContext;
use App\Payments\DTOs\PaymentResult;
use App\Payments\DTOs\SubscriptionContext;

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
        throw new \Exception('StripeGatewayAdapter::initiatePayment() not implemented. See Phase 1 implementation.');
    }

    /**
     * Confirm a payment from Stripe webhook.
     *
     * @param  GatewayCallbackPayload  $payload  Webhook payload from Stripe
     * @return PaymentResult Result with payment status and metadata
     *
     * @throws \Exception If payment confirmation fails
     */
    public function confirmPayment(GatewayCallbackPayload $payload): PaymentResult
    {
        throw new \Exception('StripeGatewayAdapter::confirmPayment() not implemented. See Phase 1 implementation.');
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
        throw new \Exception('StripeGatewayAdapter::cancelSubscription() not implemented. See Phase 1 implementation.');
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
