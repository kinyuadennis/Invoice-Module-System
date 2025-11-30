<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformFee extends Model
{
    protected $fillable = [
        'company_id',
        'invoice_id',
        'fee_amount',
        'fee_rate',
        'fee_status',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
        'fee_rate' => 'decimal:2',
    ];

    /**
     * The company this platform fee belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The invoice this platform fee belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
