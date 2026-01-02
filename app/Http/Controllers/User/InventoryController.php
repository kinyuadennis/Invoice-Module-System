<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Http\Services\InventoryService;
use App\Models\InventoryItem;
use App\Models\Item;
use App\Models\Supplier;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $query = InventoryItem::where('company_id', $companyId)
            ->with(['supplier', 'item'])
            ->latest();

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Stock status filter
        if ($request->has('stock_status') && $request->stock_status) {
            switch ($request->stock_status) {
                case 'low_stock':
                    $query->where('track_stock', true)
                        ->whereColumn('current_stock', '<=', 'minimum_stock');
                    break;
                case 'out_of_stock':
                    $query->where('track_stock', true)
                        ->where('current_stock', '<=', 0);
                    break;
                case 'in_stock':
                    $query->where('track_stock', true)
                        ->whereColumn('current_stock', '>', 'minimum_stock');
                    break;
            }
        }

        // Active filter
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        $inventoryItems = $query->paginate(15)->through(function (InventoryItem $item) {
            return $this->inventoryService->formatInventoryItemForList($item);
        });

        // Get categories for filter
        $categories = InventoryItem::where('company_id', $companyId)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return view('user.inventory.index', [
            'inventoryItems' => $inventoryItems,
            'stats' => $this->inventoryService->getInventoryStats($companyId),
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'stock_status', 'is_active']),
        ]);
    }

    public function create()
    {
        $companyId = CurrentCompanyService::requireId();

        $items = Item::where('company_id', $companyId)
            ->select('id', 'name', 'unit_price')
            ->get();

        $suppliers = Supplier::where('company_id', $companyId)
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();

        return view('user.inventory.create', [
            'items' => $items,
            'suppliers' => $suppliers,
        ]);
    }

    public function store(StoreInventoryItemRequest $request)
    {
        $companyId = CurrentCompanyService::requireId();
        $inventoryItem = $this->inventoryService->createInventoryItem($request);

        // Clear dashboard cache
        Cache::forget("dashboard_data_{$companyId}");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Inventory item created successfully.',
                'inventory_item_id' => $inventoryItem->id,
                'redirect' => route('user.inventory.show', $inventoryItem->id),
            ]);
        }

        return redirect()->route('user.inventory.show', $inventoryItem->id)
            ->with('success', 'Inventory item created successfully.');
    }

    public function show($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $inventoryItem = InventoryItem::where('company_id', $companyId)
            ->with(['supplier', 'item', 'stockMovements.user'])
            ->findOrFail($id);

        return view('user.inventory.show', [
            'inventoryItem' => $this->inventoryService->formatInventoryItemForShow($inventoryItem),
        ]);
    }

    public function edit($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $inventoryItem = InventoryItem::where('company_id', $companyId)
            ->with(['supplier', 'item'])
            ->findOrFail($id);

        $items = Item::where('company_id', $companyId)
            ->select('id', 'name', 'unit_price')
            ->get();

        $suppliers = Supplier::where('company_id', $companyId)
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();

        return view('user.inventory.edit', [
            'inventoryItem' => $this->inventoryService->formatInventoryItemForShow($inventoryItem),
            'items' => $items,
            'suppliers' => $suppliers,
        ]);
    }

    public function update(UpdateInventoryItemRequest $request, $id)
    {
        $companyId = CurrentCompanyService::requireId();

        $inventoryItem = InventoryItem::where('company_id', $companyId)
            ->findOrFail($id);

        $this->inventoryService->updateInventoryItem($inventoryItem, $request);

        // Clear dashboard cache
        Cache::forget("dashboard_data_{$companyId}");

        return redirect()->route('user.inventory.show', $inventoryItem->id)
            ->with('success', 'Inventory item updated successfully.');
    }

    public function destroy($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $inventoryItem = InventoryItem::where('company_id', $companyId)
            ->findOrFail($id);

        // Prevent deletion if there are stock movements
        if ($inventoryItem->stockMovements()->count() > 0) {
            return back()->withErrors([
                'message' => 'Cannot delete inventory item with stock movement history. Deactivate it instead.',
            ]);
        }

        $inventoryItem->delete();

        // Clear dashboard cache
        Cache::forget("dashboard_data_{$companyId}");

        return redirect()->route('user.inventory.index')
            ->with('success', 'Inventory item deleted successfully.');
    }

    /**
     * Record a stock purchase
     */
    public function recordPurchase($id, Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'nullable|numeric|min:0',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $inventoryItem = InventoryItem::where('company_id', $companyId)
            ->findOrFail($id);

        if (! $inventoryItem->track_stock) {
            return back()->withErrors([
                'message' => 'Stock tracking is disabled for this item.',
            ]);
        }

        $this->inventoryService->recordStockMovement(
            $inventoryItem,
            'purchase',
            $request->input('quantity'),
            $request->user(),
            null,
            null,
            null,
            $request->input('notes'),
            $request->input('reference_number'),
            $request->input('unit_cost')
        );

        // Clear dashboard cache
        Cache::forget("dashboard_data_{$companyId}");

        return back()->with('success', 'Stock purchase recorded successfully.');
    }

    /**
     * Record a stock adjustment
     */
    public function recordAdjustment($id, Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $request->validate([
            'quantity' => 'required|numeric',
            'notes' => 'required|string|max:500',
        ]);

        $inventoryItem = InventoryItem::where('company_id', $companyId)
            ->findOrFail($id);

        if (! $inventoryItem->track_stock) {
            return back()->withErrors([
                'message' => 'Stock tracking is disabled for this item.',
            ]);
        }

        $this->inventoryService->recordStockMovement(
            $inventoryItem,
            'adjustment',
            $request->input('quantity'),
            $request->user(),
            null,
            null,
            null,
            $request->input('notes')
        );

        // Clear dashboard cache
        Cache::forget("dashboard_data_{$companyId}");

        return back()->with('success', 'Stock adjustment recorded successfully.');
    }
}
