<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PaymentTransaction Model
 *
 * Tracks payment gateway transactions for webhook processing and audit.
 * Stores transaction references to link webhooks to invoices.
 */
class PaymentTransaction extends Model
{
    protected $fillable = [
        'company_id',
        'invoice_id',
        'gateway',
        'transaction_reference',
        'transaction_id',
        'status',
        'amount',
        'gateway_payload',
        'metadata',
        'initiated_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_payload' => 'array',
        'metadata' => 'array',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * The company this transaction belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The invoice this transaction is for.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
