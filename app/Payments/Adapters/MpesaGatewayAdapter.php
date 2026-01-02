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
 * M-Pesa Gateway Adapter
 *
 * Implements PaymentGatewayInterface for M-Pesa Daraja API.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 5.1
 *
 * M-Pesa-specific behavior:
 * - Manual, system-driven renewals (no native recurring support)
 * - STK Push for payment initiation
 * - Callback-based confirmation
 * - No native subscription cancellation (throws UnsupportedOperationException)
 */
class MpesaGatewayAdapter implements PaymentGatewayInterface
{
    /**
     * Initiate a payment request with M-Pesa STK Push.
     *
     * @param  PaymentContext  $context  Payment context
     * @return GatewayResponse Response with CheckoutRequestID and metadata
     *
     * @throws \Exception If STK Push initiation fails
     */
    public function initiatePayment(PaymentContext $context): GatewayResponse
    {
        throw new \Exception('MpesaGatewayAdapter::initiatePayment() not implemented. See Phase 1 implementation.');
    }

    /**
     * Confirm a payment from M-Pesa callback.
     *
     * @param  GatewayCallbackPayload  $payload  Callback payload from M-Pesa
     * @return PaymentResult Result with payment status and metadata
     *
     * @throws \Exception If payment confirmation fails
     */
    public function confirmPayment(GatewayCallbackPayload $payload): PaymentResult
    {
        throw new \Exception('MpesaGatewayAdapter::confirmPayment() not implemented. See Phase 1 implementation.');
    }

    /**
     * Cancel a subscription with M-Pesa.
     *
     * M-Pesa does not support native subscription cancellation.
     * This method always throws UnsupportedOperationException.
     *
     * @param  SubscriptionContext  $context  Subscription context
     * @return GatewayResult Never returns (always throws)
     *
     * @throws \UnsupportedOperationException Always thrown - M-Pesa does not support cancellation
     */
    public function cancelSubscription(SubscriptionContext $context): GatewayResult
    {
        throw new \UnsupportedOperationException('M-Pesa does not support subscription cancellation. Cancellation is handled internally.');
    }

    /**
     * Check if M-Pesa supports recurring payments.
     *
     * @return bool Always returns false - M-Pesa has no native recurring support
     */
    public function supportsRecurring(): bool
    {
        return false;
    }
}
