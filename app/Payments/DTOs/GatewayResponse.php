<?php

namespace App\Payments\DTOs;

/**
 * Gateway Response Data Transfer Object
 *
 * Response from gateway after initiating a payment.
 * Immutable value object for type safety.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
readonly class GatewayResponse
{
    /**
     * @param  string  $transactionId  Gateway transaction identifier
     *                                 - M-Pesa: CheckoutRequestID
     *                                 - Stripe: PaymentIntent ID
     * @param  string|null  $clientSecret  Client secret for frontend confirmation (Stripe only, null for M-Pesa)
     * @param  bool  $success  Whether the initiation was successful
     * @param  array<string, mixed>  $metadata  Gateway-specific metadata
     *                                          - M-Pesa: ResponseCode, CustomerMessage, etc.
     *                                          - Stripe: PaymentIntent details, etc.
     */
    public function __construct(
        public string $transactionId,
        public ?string $clientSecret,
        public bool $success,
        public array $metadata = [],
    ) {}
}
