@extends('layouts.user')

@section('title', 'Edit Inventory Item')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Edit Inventory Item</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Update inventory item details</p>
    </div>

    <form method="POST" action="{{ route('user.inventory.update', $inventoryItem['id']) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Basic Information</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input 
                        type="text" 
                        name="name" 
                        label="Item Name *" 
                        value="{{ old('name', $inventoryItem['name']) }}"
                        required
                    />
                </div>

                <div>
                    <x-select 
                        name="item_id" 
                        label="Link to Item (Optional)"
                        :options="array_merge([['value' => '', 'label' => 'None']], $items->map(function($i) { return ['value' => $i->id, 'label' => $i->name . ' (KES ' . number_format($i->unit_price, 2) . ')']; })->toArray())"
                        value="{{ old('item_id', $inventoryItem['item_id'] ?? '') }}"
                    />
                </div>

                <div>
                    <x-input 
                        type="text" 
                        name="sku" 
                        label="SKU" 
                        value="{{ old('sku', $inventoryItem['sku'] ?? '') }}"
                    />
                </div>

                <div>
                    <x-select 
                        name="supplier_id" 
                        label="Supplier"
                        :options="array_merge([['value' => '', 'label' => 'No Supplier']], $suppliers->map(function($s) { return ['value' => $s->id, 'label' => $s->name]; })->toArray())"
                        value="{{ old('supplier_id', $inventoryItem['supplier_id'] ?? '') }}"
                    />
                </div>

                <div>
                    <x-input 
                        type="text" 
                        name="category" 
                        label="Category" 
                        value="{{ old('category', $inventoryItem['category'] ?? '') }}"
                    />
                </div>

                <div>
                    <x-input 
                        type="text" 
                        name="unit_of_measure" 
                        label="Unit of Measure" 
                        value="{{ old('unit_of_measure', $inventoryItem['unit_of_measure'] ?? 'pcs') }}"
                    />
                </div>

                <div>
                    <x-input 
                        type="text" 
                        name="location" 
                        label="Location/Warehouse" 
                        value="{{ old('location', $inventoryItem['location'] ?? '') }}"
                    />
                </div>

                <div>
                    <x-input 
                        type="text" 
                        name="barcode" 
                        label="Barcode" 
                        value="{{ old('barcode', $inventoryItem['barcode'] ?? '') }}"
                    />
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Description</label>
                    <textarea 
                        name="description" 
                        rows="3"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    >{{ old('description', $inventoryItem['description'] ?? '') }}</textarea>
                </div>
            </div>
        </x-card>

        <!-- Pricing -->
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Pricing</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                    <x-input 
                        type="number" 
                        name="cost_price" 
                        label="Cost Price" 
                        value="{{ old('cost_price', $inventoryItem['cost_price'] ?? 0) }}"
                        step="0.01"
                        min="0"
                    />
                </div>

                <div>
                    <x-input 
                        type="number" 
                        name="selling_price" 
                        label="Selling Price" 
                        value="{{ old('selling_price', $inventoryItem['selling_price'] ?? 0) }}"
                        step="0.01"
                        min="0"
                    />
                </div>

                <div>
                    <x-input 
                        type="number" 
                        name="unit_price" 
                        label="Unit Price" 
                        value="{{ old('unit_price', $inventoryItem['selling_price'] ?? 0) }}"
                        step="0.01"
                        min="0"
                    />
                </div>
            </div>
        </x-card>

        <!-- Stock Management -->
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Stock Management</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="track_stock" 
                        id="track_stock"
                        value="1"
                        {{ ($inventoryItem['track_stock'] ?? false) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        onchange="toggleStockFields()"
                    >
                    <label for="track_stock" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-200">Track Stock</label>
                </div>

                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="auto_deduct_on_invoice" 
                        id="auto_deduct_on_invoice"
                        value="1"
                        {{ ($inventoryItem['auto_deduct_on_invoice'] ?? false) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <label for="auto_deduct_on_invoice" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-200">Auto-deduct on Invoice</label>
                </div>

                <div id="stock_fields">
                    <x-input 
                        type="number" 
                        name="current_stock" 
                        label="Current Stock" 
                        value="{{ old('current_stock', $inventoryItem['current_stock'] ?? 0) }}"
                        step="0.01"
                        min="0"
                    />
                    <p class="mt-1 text-xs text-gray-500">Changing this will create a stock adjustment</p>
                </div>

                <div id="stock_fields">
                    <x-input 
                        type="number" 
                        name="minimum_stock" 
                        label="Minimum Stock (Reorder Point)" 
                        value="{{ old('minimum_stock', $inventoryItem['minimum_stock'] ?? 0) }}"
                        step="0.01"
                        min="0"
                    />
                </div>

                <div id="stock_fields">
                    <x-input 
                        type="number" 
                        name="maximum_stock" 
                        label="Maximum Stock" 
                        value="{{ old('maximum_stock', $inventoryItem['maximum_stock'] ?? '') }}"
                        step="0.01"
                        min="0"
                    />
                </div>

                <div id="stock_fields" class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Stock Adjustment Reason (if changing stock)</label>
                    <textarea 
                        name="stock_adjustment_reason" 
                        rows="2"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Reason for stock adjustment..."
                    >{{ old('stock_adjustment_reason') }}</textarea>
                </div>
            </div>
        </x-card>

        <!-- Status -->
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Status</h2>
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    name="is_active" 
                    id="is_active"
                    value="1"
                    {{ ($inventoryItem['is_active'] ?? true) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >
                <label for="is_active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-200">Active</label>
            </div>
        </x-card>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('user.inventory.show', $inventoryItem['id']) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-50">
                Cancel
            </a>
            <x-button type="submit" variant="primary">Update Item</x-button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function toggleStockFields() {
        const trackStock = document.getElementById('track_stock').checked;
        const stockFields = document.querySelectorAll('#stock_fields');
        stockFields.forEach(field => {
            const inputs = field.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.disabled = !trackStock;
            });
        });
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', toggleStockFields);
</script>
@endpush
@endsection

