<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
    ];

    /**
     * The invoice this item belongs to.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
