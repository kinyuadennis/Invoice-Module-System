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
        'transaction_id',
        'transaction_reference',
        'gateway_metadata',
        'paid_at',
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
