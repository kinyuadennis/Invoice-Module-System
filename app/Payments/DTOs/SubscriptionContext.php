<?php

namespace App\Payments\DTOs;

/**
 * Subscription Context Data Transfer Object
 *
 * Contains information needed to cancel a subscription with a gateway.
 * Immutable value object for type safety.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
readonly class SubscriptionContext
{
    /**
     * @param  string  $subscriptionId  Internal subscription UUID (database ID)
     * @param  string|null  $gatewaySubscriptionId  Gateway subscription identifier (e.g., Stripe subscription ID, null for M-Pesa)
     * @param  string  $reason  Cancellation reason for audit log
     */
    public function __construct(
        public string $subscriptionId,
        public ?string $gatewaySubscriptionId,
        public string $reason,
    ) {}
}
