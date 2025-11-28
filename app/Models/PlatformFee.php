<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformFee extends Model
{
    protected $fillable = [
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
     * The invoice this platform fee is associated with.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
