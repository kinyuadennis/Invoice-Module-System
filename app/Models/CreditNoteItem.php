<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNoteItem extends Model
{
    protected $fillable = [
        'company_id',
        'credit_note_id',
        'invoice_item_id',
        'item_id',
        'description',
        'quantity',
        'unit_price',
        'vat_included',
        'vat_rate',
        'total_price',
        'credit_reason',
        'credit_reason_details',
    ];

    protected function casts(): array
    {
        return [
            'vat_included' => 'boolean',
            'vat_rate' => 'decimal:2',
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
