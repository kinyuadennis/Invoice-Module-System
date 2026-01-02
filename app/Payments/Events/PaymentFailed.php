<?php

namespace App\Payments\Events;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Payment Failed Event
 *
 * Fired when a payment fails.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
class PaymentFailed
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Payment $payment
    ) {}
}
