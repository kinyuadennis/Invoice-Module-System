<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformFee extends Model
{
    protected $fillable = [
        'invoice_id',
        'fee_amount',
        'fee_status',
    ];

    /**
     * The invoice this platform fee is associated with.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
