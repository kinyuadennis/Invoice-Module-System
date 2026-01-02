<?php

namespace App\Payments\DTOs;

/**
 * Gateway Callback Payload Data Transfer Object
 *
 * Raw payload from gateway callback/webhook.
 * Immutable value object for type safety.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
readonly class GatewayCallbackPayload
{
    /**
     * @param  array<string, mixed>  $rawData  Full JSON payload from callback/webhook
     *                                         - M-Pesa: Body.stkCallback with ResultCode, CallbackMetadata, etc.
     *                                         - Stripe: Event object with data.object containing payment details
     * @param  string  $gatewayReference  Gateway reference identifier
     *                                    - M-Pesa: CheckoutRequestID
     *                                    - Stripe: Event ID or PaymentIntent ID
     * @param  string|null  $signature  Signature for verification (Stripe only, null for M-Pesa)
     *                                  - Stripe: Webhook signature for verification
     *                                  - M-Pesa: Not applicable (uses IP whitelist)
     */
    public function __construct(
        public array $rawData,
        public string $gatewayReference,
        public ?string $signature = null,
    ) {}
}
