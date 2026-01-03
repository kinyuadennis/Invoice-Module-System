<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Payment Attempt Model
 *
 * Tracks all payment attempts (initiated, pending, succeeded, failed, timed_out)
 * without polluting the immutable payments table.
 *
 * Per blueprint: Payments table contains ONLY succeeded payments.
 * All attempts (including failures) are tracked here.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 2.1
 */
class PaymentAttempt extends Model
{
    protected $fillable = [
        'subscription_id',
        'amount',
        'currency',
        'gateway',
        'gateway_transaction_id',
        'attempt_number',
        'status',
        'error_code',
        'error_message',
        'gateway_metadata',
        'raw_gateway_payload',
        'idempotency_key',
        'initiated_at',
        'succeeded_at',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'attempt_number' => 'integer',
            'gateway_metadata' => 'array',
            'raw_gateway_payload' => 'array',
            'initiated_at' => 'datetime',
            'succeeded_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    /**
     * The subscription this payment attempt belongs to.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Check if attempt is in terminal state.
     */
    public function isTerminal(): bool
    {
        return in_array($this->status, [
            self::STATUS_SUCCEEDED,
            self::STATUS_FAILED,
            self::STATUS_TIMED_OUT,
        ]);
    }

    /**
     * Transition attempt to succeeded.
     *
     * @throws \Exception If already in terminal state
     */
    public function markAsSucceeded(): void
    {
        if ($this->isTerminal()) {
            throw new \Exception("Payment attempt is already in terminal state: {$this->status}");
        }

        $this->update([
            'status' => self::STATUS_SUCCEEDED,
            'succeeded_at' => now(),
        ]);
    }

    /**
     * Transition attempt to failed.
     *
     * @throws \Exception If already in terminal state
     */
    public function markAsFailed(?string $errorCode = null, ?string $errorMessage = null): void
    {
        if ($this->isTerminal()) {
            throw new \Exception("Payment attempt is already in terminal state: {$this->status}");
        }

        $this->update([
            'status' => self::STATUS_FAILED,
            'failed_at' => now(),
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Transition attempt to timed_out.
     *
     * @throws \Exception If already in terminal state
     */
    public function markAsTimedOut(): void
    {
        if ($this->isTerminal()) {
            throw new \Exception("Payment attempt is already in terminal state: {$this->status}");
        }

        $this->update([
            'status' => self::STATUS_TIMED_OUT,
            'failed_at' => now(),
        ]);
    }

    /**
     * Transition attempt to pending (gateway accepted).
     */
    public function markAsPending(): void
    {
        if ($this->status !== self::STATUS_INITIATED) {
            throw new \Exception("Can only transition from initiated to pending. Current status: {$this->status}");
        }

        $this->update([
            'status' => self::STATUS_PENDING,
        ]);
    }

    // Status constants
    public const STATUS_INITIATED = 'initiated';

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_FAILED = 'failed';

    public const STATUS_TIMED_OUT = 'timed_out';
}
