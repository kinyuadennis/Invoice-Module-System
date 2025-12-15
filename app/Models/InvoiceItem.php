<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'company_id',
        'invoice_id',
        'item_id',
        'description',
        'quantity',
        'unit_price',
        'vat_included',
        'vat_rate',
        'total_price',
    ];

    protected $casts = [
        'vat_included' => 'boolean',
        'vat_rate' => 'decimal:2',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Prevent modification of invoice items when parent invoice is finalized
        static::saving(function ($invoiceItem) {
            if ($invoiceItem->exists || $invoiceItem->invoice_id) {
                $invoice = $invoiceItem->invoice ?? Invoice::find($invoiceItem->invoice_id);
                if ($invoice && $invoice->isFinalized()) {
                    $invoiceNumber = $invoice->invoice_number ?? $invoice->id;
                    throw new \DomainException(
                        "Cannot modify invoice items on finalized invoice #{$invoiceNumber}. Items are immutable after finalization."
                    );
                }
            }
        });

        // Prevent deletion of invoice items when parent invoice is finalized
        static::deleting(function ($invoiceItem) {
            $invoice = $invoiceItem->invoice ?? Invoice::find($invoiceItem->invoice_id);
            if ($invoice && $invoice->isFinalized()) {
                $invoiceNumber = $invoice->invoice_number ?? $invoice->id;
                throw new \DomainException(
                    "Cannot delete invoice items on finalized invoice #{$invoiceNumber}. Items are immutable after finalization."
                );
            }
        });
    }

    /**
     * The company this invoice item belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The invoice this item belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * The reusable item this invoice item is linked to (nullable).
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
