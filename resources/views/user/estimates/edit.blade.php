@extends('layouts.user')

@section('title', 'Edit Estimate')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Edit Estimate</h1>
        <p class="mt-1 text-sm text-gray-600">Update estimate details</p>
    </div>

    <form method="POST" action="{{ route('user.estimates.update', $estimate['id'] ?? 0) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Estimate Header -->
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Estimate Details</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <x-select 
                    name="client_id" 
                    label="Client"
                    :options="array_merge([['value' => '', 'label' => 'Select Client']], $clients->map(function($c) { return ['value' => $c->id, 'label' => $c->name]; })->toArray())"
                    value="{{ old('client_id', $estimate['client']['id'] ?? '') }}"
                />
                
                <x-input 
                    type="date" 
                    name="issue_date" 
                    label="Issue Date"
                    value="{{ old('issue_date', $estimate['issue_date'] ?? now()->toDateString()) }}"
                />
                
                <x-input 
                    type="date" 
                    name="expiry_date" 
                    label="Expiry Date (Optional)"
                    value="{{ old('expiry_date', $estimate['expiry_date'] ?? '') }}"
                />
                
                <x-input 
                    type="text" 
                    name="po_number" 
                    label="PO Number (Optional)"
                    value="{{ old('po_number', $estimate['po_number'] ?? '') }}"
                />
            </div>
        </x-card>

        <!-- Items -->
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Items</h2>
            <div id="items-container" class="space-y-4">
                @if(isset($estimate['items']) && count($estimate['items']) > 0)
                    @foreach($estimate['items'] as $index => $item)
                        <div class="grid grid-cols-12 gap-4 items-end item-row">
                            <div class="col-span-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <input type="text" name="items[{{ $index }}][description]" value="{{ $item['description'] }}" class="w-full rounded-lg border-gray-300" required>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] }}" step="0.01" min="0.01" class="w-full rounded-lg border-gray-300" required>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price</label>
                                <input type="number" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] }}" step="0.01" min="0" class="w-full rounded-lg border-gray-300" required>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                                <input type="text" value="KES {{ number_format($item['total_price'], 2) }}" class="w-full rounded-lg border-gray-300 bg-gray-50" readonly>
                                <input type="hidden" name="items[{{ $index }}][total_price]" value="{{ $item['total_price'] }}">
                            </div>
                            <div class="col-span-1">
                                <button type="button" onclick="this.closest('.item-row').remove()" class="w-full px-3 py-2 text-red-600 hover:text-red-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="grid grid-cols-12 gap-4 items-end item-row">
                        <div class="col-span-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <input type="text" name="items[0][description]" class="w-full rounded-lg border-gray-300" required>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                            <input type="number" name="items[0][quantity]" value="1" step="0.01" min="0.01" class="w-full rounded-lg border-gray-300" required>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price</label>
                            <input type="number" name="items[0][unit_price]" step="0.01" min="0" class="w-full rounded-lg border-gray-300" required>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                            <input type="text" value="KES 0.00" class="w-full rounded-lg border-gray-300 bg-gray-50" readonly>
                            <input type="hidden" name="items[0][total_price]" value="0">
                        </div>
                        <div class="col-span-1">
                            <button type="button" onclick="this.closest('.item-row').remove()" class="w-full px-3 py-2 text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
            <button type="button" onclick="addItemRow()" class="mt-4 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                + Add Item
            </button>
        </x-card>

        <!-- Notes & Terms -->
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Additional Information</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="3" class="w-full rounded-lg border-gray-300">{{ old('notes', $estimate['notes'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Terms & Conditions</label>
                    <textarea name="terms_and_conditions" rows="3" class="w-full rounded-lg border-gray-300">{{ old('terms_and_conditions', $estimate['terms_and_conditions'] ?? '') }}</textarea>
                </div>
            </div>
        </x-card>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('user.estimates.show', $estimate['id']) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <x-button type="submit" variant="primary">Update Estimate</x-button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function addItemRow() {
        const container = document.getElementById('items-container');
        const index = container.children.length;
        const row = document.createElement('div');
        row.className = 'grid grid-cols-12 gap-4 items-end item-row';
        row.innerHTML = `
            <div class="col-span-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <input type="text" name="items[${index}][description]" class="w-full rounded-lg border-gray-300" required>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                <input type="number" name="items[${index}][quantity]" value="1" step="0.01" min="0.01" class="w-full rounded-lg border-gray-300" required>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price</label>
                <input type="number" name="items[${index}][unit_price]" step="0.01" min="0" class="w-full rounded-lg border-gray-300" required>
            </div>
            <div class="col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                <input type="text" value="KES 0.00" class="w-full rounded-lg border-gray-300 bg-gray-50" readonly>
                <input type="hidden" name="items[${index}][total_price]" value="0">
            </div>
            <div class="col-span-1">
                <button type="button" onclick="this.closest('.item-row').remove()" class="w-full px-3 py-2 text-red-600 hover:text-red-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        `;
        container.appendChild(row);
        
        // Add calculation logic
        const quantityInput = row.querySelector('input[name*="[quantity]"]');
        const priceInput = row.querySelector('input[name*="[unit_price]"]');
        const totalInput = row.querySelector('input[readonly]');
        const totalHidden = row.querySelector('input[name*="[total_price]"]');
        
        function calculateTotal() {
            const qty = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const total = qty * price;
            totalInput.value = 'KES ' + total.toFixed(2);
            totalHidden.value = total.toFixed(2);
        }
        
        quantityInput.addEventListener('input', calculateTotal);
        priceInput.addEventListener('input', calculateTotal);
    }
    
    // Add calculation to existing rows
    document.querySelectorAll('.item-row').forEach(row => {
        const quantityInput = row.querySelector('input[name*="[quantity]"]');
        const priceInput = row.querySelector('input[name*="[unit_price]"]');
        const totalInput = row.querySelector('input[readonly]');
        const totalHidden = row.querySelector('input[name*="[total_price]"]');
        
        if (quantityInput && priceInput && totalInput && totalHidden) {
            function calculateTotal() {
                const qty = parseFloat(quantityInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const total = qty * price;
                totalInput.value = 'KES ' + total.toFixed(2);
                totalHidden.value = total.toFixed(2);
            }
            
            quantityInput.addEventListener('input', calculateTotal);
            priceInput.addEventListener('input', calculateTotal);
        }
    });
</script>
@endpush
@endsection

