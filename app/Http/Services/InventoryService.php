<?php

namespace App\Http\Services;

use App\Models\Company;
use App\Models\CreditNote;
use App\Models\Estimate;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\StockMovement;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;

class InventoryService
{
    /**
     * Create a new inventory item
     */
    public function createInventoryItem(Request $request): InventoryItem
    {
        $companyId = CurrentCompanyService::requireId();

        $data = $request->only([
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
        ]);

        $data['company_id'] = $companyId;
        $data['track_stock'] = $data['track_stock'] ?? true;
        $data['auto_deduct_on_invoice'] = $data['auto_deduct_on_invoice'] ?? true;
        $data['is_active'] = $data['is_active'] ?? true;
        $data['current_stock'] = $data['current_stock'] ?? 0;

        $inventoryItem = InventoryItem::create($data);

        // Create opening stock movement if initial stock provided
        if ($inventoryItem->track_stock && $inventoryItem->current_stock > 0) {
            $this->recordStockMovement(
                $inventoryItem,
                'opening_stock',
                $inventoryItem->current_stock,
                $request->user(),
                null,
                null,
                null,
                'Opening stock'
            );
        }

        return $inventoryItem;
    }

    /**
     * Update an existing inventory item
     */
    public function updateInventoryItem(InventoryItem $inventoryItem, Request $request): InventoryItem
    {
        $oldStock = $inventoryItem->current_stock;

        $data = $request->only([
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
            'minimum_stock',
            'maximum_stock',
            'track_stock',
            'auto_deduct_on_invoice',
            'location',
            'barcode',
            'is_active',
        ]);

        // Handle stock adjustment if current_stock changed
        if ($request->has('current_stock') && $inventoryItem->track_stock) {
            $newStock = (float) $request->input('current_stock');
            $difference = $newStock - $oldStock;

            if ($difference != 0) {
                $data['current_stock'] = $newStock;

                // Record adjustment movement
                $this->recordStockMovement(
                    $inventoryItem,
                    'adjustment',
                    $difference,
                    $request->user(),
                    null,
                    null,
                    null,
                    $request->input('stock_adjustment_reason', 'Manual adjustment')
                );
            }
        }

        $inventoryItem->update($data);

        return $inventoryItem;
    }

    /**
     * Record a stock movement
     */
    public function recordStockMovement(
        InventoryItem $inventoryItem,
        string $type,
        float $quantity,
        $user,
        ?Invoice $invoice = null,
        ?Estimate $estimate = null,
        ?CreditNote $creditNote = null,
        ?string $notes = null,
        ?string $referenceNumber = null,
        ?float $unitCost = null
    ): StockMovement {
        $stockBefore = $inventoryItem->current_stock;
        $stockAfter = $stockBefore + $quantity;

        // Clamp stock to zero (never allow negative stock)
        $clampedStockAfter = max(0, $stockAfter);

        // Update inventory item stock
        if ($inventoryItem->track_stock) {
            $inventoryItem->current_stock = $clampedStockAfter;
            $inventoryItem->save();
        }

        // Create stock movement record
        // Use clamped value to match actual inventory state
        $movement = StockMovement::create([
            'company_id' => $inventoryItem->company_id,
            'inventory_item_id' => $inventoryItem->id,
            'user_id' => $user->id,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $clampedStockAfter,
            'invoice_id' => $invoice?->id,
            'estimate_id' => $estimate?->id,
            'credit_note_id' => $creditNote?->id,
            'supplier_id' => $inventoryItem->supplier_id,
            'notes' => $notes,
            'reference_number' => $referenceNumber,
            'movement_date' => now()->toDateString(),
            'unit_cost' => $unitCost ?? $inventoryItem->cost_price,
        ]);

        return $movement;
    }

    /**
     * Auto-deduct stock when invoice is created/updated
     */
    public function deductStockForInvoice(Invoice $invoice): void
    {
        foreach ($invoice->invoiceItems as $invoiceItem) {
            // Check if invoice item is linked to an inventory item
            if ($invoiceItem->item_id) {
                $inventoryItem = InventoryItem::where('company_id', $invoice->company_id)
                    ->where('item_id', $invoiceItem->item_id)
                    ->where('track_stock', true)
                    ->where('auto_deduct_on_invoice', true)
                    ->where('is_active', true)
                    ->first();

                if ($inventoryItem) {
                    // Check if stock is available
                    if (! $inventoryItem->hasStockAvailable($invoiceItem->quantity)) {
                        // Log warning but don't block invoice creation
                        \Log::warning("Insufficient stock for inventory item {$inventoryItem->id} when creating invoice {$invoice->id}");

                        continue;
                    }

                    // Deduct stock
                    $this->recordStockMovement(
                        $inventoryItem,
                        'sale',
                        -$invoiceItem->quantity, // Negative for deduction
                        $invoice->user,
                        $invoice,
                        null,
                        null,
                        "Auto-deducted for invoice {$invoice->invoice_reference}",
                        $invoice->invoice_reference
                    );
                }
            }
        }
    }

    /**
     * Restore stock when invoice is cancelled or credit note is issued
     */
    public function restoreStockForInvoice(Invoice $invoice): void
    {
        foreach ($invoice->invoiceItems as $invoiceItem) {
            if ($invoiceItem->item_id) {
                // Only restore stock for items that were auto-deducted
                // This matches the logic in deductStockForInvoice()
                $inventoryItem = InventoryItem::where('company_id', $invoice->company_id)
                    ->where('item_id', $invoiceItem->item_id)
                    ->where('track_stock', true)
                    ->where('auto_deduct_on_invoice', true)
                    ->where('is_active', true)
                    ->first();

                if ($inventoryItem) {
                    // Restore stock (positive quantity)
                    $this->recordStockMovement(
                        $inventoryItem,
                        'return',
                        $invoiceItem->quantity,
                        $invoice->user,
                        $invoice,
                        null,
                        null,
                        "Stock restored for cancelled invoice {$invoice->invoice_reference}",
                        $invoice->invoice_reference
                    );
                }
            }
        }
    }

    /**
     * Get low stock items for a company
     */
    public function getLowStockItems(int $companyId): array
    {
        return InventoryItem::where('company_id', $companyId)
            ->where('track_stock', true)
            ->where('is_active', true)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->with(['supplier', 'item'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'current_stock' => (float) $item->current_stock,
                    'minimum_stock' => (float) $item->minimum_stock,
                    'supplier' => $item->supplier?->name,
                ];
            })
            ->toArray();
    }

    /**
     * Format inventory item for list display
     */
    public function formatInventoryItemForList(InventoryItem $item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'sku' => $item->sku,
            'category' => $item->category,
            'current_stock' => (float) $item->current_stock,
            'minimum_stock' => (float) $item->minimum_stock,
            'selling_price' => (float) $item->selling_price,
            'cost_price' => (float) $item->cost_price,
            'track_stock' => $item->track_stock,
            'is_low_stock' => $item->isLowStock(),
            'is_out_of_stock' => $item->isOutOfStock(),
            'supplier' => $item->supplier?->name,
            'unit_of_measure' => $item->unit_of_measure,
        ];
    }

    /**
     * Format inventory item with full details
     */
    public function formatInventoryItemForShow(InventoryItem $item): array
    {
        $data = $this->formatInventoryItemForList($item);

        $data['description'] = $item->description;
        $data['location'] = $item->location;
        $data['barcode'] = $item->barcode;
        $data['maximum_stock'] = $item->maximum_stock ? (float) $item->maximum_stock : null;
        $data['auto_deduct_on_invoice'] = $item->auto_deduct_on_invoice;
        $data['is_active'] = $item->is_active;
        $data['item_id'] = $item->item_id;
        $data['supplier_id'] = $item->supplier_id;
        $data['recent_movements'] = $item->stockMovements()
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($movement) {
                return [
                    'id' => $movement->id,
                    'type' => $movement->type,
                    'quantity' => (float) $movement->quantity,
                    'stock_after' => (float) $movement->stock_after,
                    'movement_date' => $movement->movement_date->toDateString(),
                    'notes' => $movement->notes,
                ];
            });

        return $data;
    }

    /**
     * Get inventory statistics
     */
    public function getInventoryStats(int $companyId): array
    {
        $query = InventoryItem::where('company_id', $companyId)
            ->where('track_stock', true)
            ->where('is_active', true);

        return [
            'total_items' => (clone $query)->count(),
            'low_stock_count' => (clone $query)->whereColumn('current_stock', '<=', 'minimum_stock')->count(),
            'out_of_stock_count' => (clone $query)->where('current_stock', '<=', 0)->count(),
            'total_stock_value' => (float) (clone $query)->sum(\DB::raw('current_stock * cost_price')),
        ];
    }
}
