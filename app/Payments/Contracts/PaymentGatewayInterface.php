<?php

namespace App\Payments\Contracts;

use App\Payments\DTOs\GatewayCallbackPayload;
use App\Payments\DTOs\GatewayResponse;
use App\Payments\DTOs\GatewayResult;
use App\Payments\DTOs\PaymentContext;
use App\Payments\DTOs\PaymentResult;
use App\Payments\DTOs\SubscriptionContext;

/**
 * Payment Gateway Interface
 *
 * Defines the contract for payment gateway adapters.
 * Gateway-agnostic interface that allows different payment providers
 * (M-Pesa, Stripe) to be used interchangeably.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 3
 *
 * Rules:
 * - No shared retry logic
 * - No shared webhook validation
 * - No shared lifecycle abstraction
 * - Gateway adapters must never mutate domain models directly
 * - Gateway adapters must never decide subscription truth
 * - Gateway adapters must never create invoices
 */
interface PaymentGatewayInterface
{
    /**
     * Initiate a payment request with the gateway.
     *
     * @param  PaymentContext  $context  Payment context containing subscription, amount, user details, etc.
     * @return GatewayResponse Response containing transaction ID, client secret (if applicable), and metadata
     *
     * @throws \Exception If payment initiation fails
     */
    public function initiatePayment(PaymentContext $context): GatewayResponse;

    /**
     * Confirm a payment from a gateway callback/webhook.
     *
     * @param  GatewayCallbackPayload  $payload  Callback payload from gateway
     * @return PaymentResult Result containing payment status, ID, and metadata
     *
     * @throws \Exception If payment confirmation fails
     */
    public function confirmPayment(GatewayCallbackPayload $payload): PaymentResult;

    /**
     * Cancel a subscription with the gateway.
     *
     * @param  SubscriptionContext  $context  Subscription context containing subscription ID, gateway subscription ID, reason
     * @return GatewayResult Result indicating success or failure with error message
     *
     * @throws \UnsupportedOperationException If gateway does not support subscription cancellation
     * @throws \Exception If cancellation fails
     */
    public function cancelSubscription(SubscriptionContext $context): GatewayResult;

    /**
     * Check if the gateway supports recurring payments/subscriptions.
     *
     * @return bool True if gateway supports recurring payments, false otherwise
     */
    public function supportsRecurring(): bool;
}
