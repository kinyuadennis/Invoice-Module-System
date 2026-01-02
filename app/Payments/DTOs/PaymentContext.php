<?php

namespace App\Payments\DTOs;

/**
 * Payment Context Data Transfer Object
 *
 * Contains all information needed to initiate a payment with a gateway.
 * Immutable value object for type safety.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
readonly class PaymentContext
{
    /**
     * @param  string  $subscriptionId  Subscription UUID (internal database ID)
     * @param  float  $amount  Payment amount
     * @param  string  $currency  Currency ISO code (e.g., 'KES', 'USD')
     * @param  array<string, string>  $userDetails  User details array with keys:
     *                                              - 'phone' (for M-Pesa): Phone number
     *                                              - 'customerId' (for Stripe): Stripe customer ID
     *                                              - 'country' (for routing): Country code
     * @param  string  $reference  Unique payment reference (e.g., 'SUB_123')
     * @param  string  $description  Payment description
     */
    public function __construct(
        public string $subscriptionId,
        public float $amount,
        public string $currency,
        public array $userDetails,
        public string $reference,
        public string $description,
    ) {}
}
