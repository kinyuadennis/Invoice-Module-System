<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'company_id',
        'invoice_id',
        'payment_date',
        'amount',
        'refunded_amount',
        'payment_method',
        'mpesa_reference',
        'notes',
        'paid_at',
        'gateway',
        'gateway_transaction_id',
        'gateway_payment_intent_id',
        'gateway_metadata',
        'gateway_status',
        'status',
        'fraud_status',
        'fraud_score',
        'fraud_checks',
        'fraud_reason',
        'ip_address',
        'user_agent',
        'device_fingerprint',
        'fraud_reviewed_at',
        'fraud_reviewed_by',
        'retry_count',
        'last_retry_at',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'gateway_metadata' => 'array',
        'fraud_score' => 'decimal:2',
        'fraud_checks' => 'array',
        'fraud_reviewed_at' => 'datetime',
        'last_retry_at' => 'datetime',
    ];

    /**
     * The company this payment belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The invoice this payment belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * The refunds for this payment.
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * The bank transaction matched to this payment.
     */
    public function bankTransaction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BankTransaction::class);
    }

    /**
     * The user who reviewed this payment for fraud.
     */
    public function fraudReviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fraud_reviewed_by');
    }

    /**
     * Get the net payment amount (amount - refunded_amount).
     */
    public function getNetAmountAttribute(): float
    {
        return (float) $this->amount - (float) ($this->refunded_amount ?? 0);
    }

    /**
     * Check if payment is fully refunded.
     */
    public function isFullyRefunded(): bool
    {
        return (float) ($this->refunded_amount ?? 0) >= (float) $this->amount;
    }

    /**
     * Check if payment is partially refunded.
     */
    public function isPartiallyRefunded(): bool
    {
        $refunded = (float) ($this->refunded_amount ?? 0);

        return $refunded > 0 && $refunded < (float) $this->amount;
    }

    /**
     * Check if payment can be refunded.
     */
    public function canBeRefunded(): bool
    {
        return $this->status === 'completed' && ! $this->isFullyRefunded();
    }
}
