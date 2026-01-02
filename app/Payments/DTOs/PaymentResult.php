<?php

namespace App\Payments\DTOs;

/**
 * Payment Result Data Transfer Object
 *
 * Result of payment confirmation from gateway callback/webhook.
 * Immutable value object for type safety.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
readonly class PaymentResult
{
    /**
     * @param  string  $status  Payment status: 'confirmed' | 'failed' | 'timeout'
     * @param  string  $paymentId  Internal database payment ID
     * @param  string  $gatewayReference  Gateway reference identifier
     * @param  array<string, mixed>  $metadata  Gateway-specific metadata
     *                                          - M-Pesa: CallbackMetadata with Amount, MpesaReceiptNumber, etc.
     *                                          - Stripe: data.object with charge details, etc.
     */
    public function __construct(
        public string $status,
        public string $paymentId,
        public string $gatewayReference,
        public array $metadata = [],
    ) {}
}
