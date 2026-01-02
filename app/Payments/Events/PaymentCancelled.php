<?php

namespace App\Payments\Events;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Payment Cancelled Event
 *
 * Fired when a payment is cancelled.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
class PaymentCancelled
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Payment $payment
    ) {}
}
