<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'company_id',
        'invoice_id',
        'payment_date',
        'amount',
        'payment_method',
        'mpesa_reference',
        'paid_at',
        'gateway',
        'gateway_transaction_id',
        'gateway_payment_intent_id',
        'gateway_metadata',
        'gateway_status',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
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
}
