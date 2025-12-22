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
    ];

    protected $casts = [
        'payment_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'gateway_metadata' => 'array',
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
