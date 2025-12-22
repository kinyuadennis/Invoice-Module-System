<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'company_id',
        'inventory_item_id',
        'user_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'invoice_id',
        'estimate_id',
        'credit_note_id',
        'supplier_id',
        'notes',
        'reference_number',
        'movement_date',
        'unit_cost',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'stock_before' => 'decimal:2',
            'stock_after' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'movement_date' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
