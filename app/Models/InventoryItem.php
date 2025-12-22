<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = [
        'company_id',
        'item_id',
        'supplier_id',
        'sku',
        'name',
        'description',
        'category',
        'unit_of_measure',
        'cost_price',
        'selling_price',
        'unit_price',
        'current_stock',
        'minimum_stock',
        'maximum_stock',
        'track_stock',
        'auto_deduct_on_invoice',
        'location',
        'barcode',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'current_stock' => 'decimal:2',
            'minimum_stock' => 'decimal:2',
            'maximum_stock' => 'decimal:2',
            'track_stock' => 'boolean',
            'auto_deduct_on_invoice' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        // Sync unit_price with selling_price
        static::saving(function ($item) {
            if (empty($item->unit_price) && ! empty($item->selling_price)) {
                $item->unit_price = $item->selling_price;
            } elseif (empty($item->selling_price) && ! empty($item->unit_price)) {
                $item->selling_price = $item->unit_price;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->track_stock && $this->current_stock <= $this->minimum_stock;
    }

    public function isOutOfStock(): bool
    {
        return $this->track_stock && $this->current_stock <= 0;
    }

    public function hasStockAvailable(float $quantity): bool
    {
        return ! $this->track_stock || $this->current_stock >= $quantity;
    }
}
