@extends('layouts.user')

@section('title', 'Create Credit Note')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Create Credit Note</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Issue a credit note for invoice {{ $invoice->full_number ?? $invoice->invoice_reference }}</p>
    </div>

    <!-- Invoice Summary -->
    <x-card>
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Original Invoice</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Invoice Number</p>
                <p class="font-semibold text-gray-900">{{ $invoice->full_number ?? $invoice->invoice_reference }}</p>
            </div>
            <div>
                <p class="text-gray-500">Client</p>
                <p class="font-semibold text-gray-900">{{ $invoice->client->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Invoice Date</p>
                <p class="font-semibold text-gray-900">{{ $invoice->issue_date->format('M d, Y') }}</p>
            </div>
            <div>
                <p class="text-gray-500">Total Amount</p>
                <p class="font-semibold text-gray-900">KES {{ number_format($invoice->grand_total ?? 0, 2) }}</p>
            </div>
        </div>
    </x-card>

    <form method="POST" action="{{ route('user.credit-notes.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

        <!-- Credit Note Details -->
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Credit Note Details</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-select 
                        name="reason" 
                        label="Reason for Credit Note *"
                        :options="[
                            ['value' => 'refund', 'label' => 'Refund'],
                            ['value' => 'adjustment', 'label' => 'Adjustment'],
                            ['value' => 'error', 'label' => 'Error Correction'],
                            ['value' => 'cancellation', 'label' => 'Cancellation'],
                            ['value' => 'other', 'label' => 'Other'],
                        ]"
                        value="{{ old('reason', 'other') }}"
                        required
                    />
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Reason Details</label>
                    <textarea 
                        name="reason_details" 
                        rows="3"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Provide details about why this credit note is being issued..."
                    >{{ old('reason_details') }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Notes</label>
                    <textarea 
                        name="notes" 
                        rows="3"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Additional notes..."
                    >{{ old('notes') }}</textarea>
                </div>
            </div>
        </x-card>

        <!-- Items to Credit -->
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Items to Credit</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">Select items from the original invoice to credit. Leave empty to credit all items.</p>
            
            <div class="space-y-4">
                @foreach($invoice->invoiceItems as $index => $invoiceItem)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <input 
                                        type="checkbox" 
                                        name="items[{{ $index }}][include]" 
                                        id="item_{{ $index }}"
                                        value="1"
                                        checked
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        onchange="toggleItemRow({{ $index }})"
                                    >
                                    <label for="item_{{ $index }}" class="font-medium text-gray-900 cursor-pointer">
                                        {{ $invoiceItem->description }}
                                    </label>
                                </div>
                                <div class="grid grid-cols-3 gap-4 text-sm ml-6">
                                    <div>
                                        <span class="text-gray-500">Quantity:</span>
                                        <span class="font-medium text-gray-900 ml-1">{{ $invoiceItem->quantity }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Unit Price:</span>
                                        <span class="font-medium text-gray-900 ml-1">KES {{ number_format($invoiceItem->unit_price, 2) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Total:</span>
                                        <span class="font-medium text-gray-900 ml-1">KES {{ number_format($invoiceItem->total_price, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ml-6 mt-3 space-y-2 item-details" id="item_details_{{ $index }}">
                            <input type="hidden" name="items[{{ $index }}][invoice_item_id]" value="{{ $invoiceItem->id }}">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200 mb-1">Quantity to Credit</label>
                                <input 
                                    type="number" 
                                    name="items[{{ $index }}][quantity]" 
                                    value="{{ $invoiceItem->quantity }}"
                                    step="0.01"
                                    min="0.01"
                                    max="{{ $invoiceItem->quantity }}"
                                    class="w-full rounded-lg border-gray-300 text-sm"
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200 mb-1">Credit Reason</label>
                                <select 
                                    name="items[{{ $index }}][credit_reason]"
                                    class="w-full rounded-lg border-gray-300 text-sm"
                                >
                                    <option value="returned">Returned</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="wrong_item">Wrong Item</option>
                                    <option value="adjustment">Adjustment</option>
                                    <option value="other" selected>Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200 mb-1">Reason Details</label>
                                <textarea 
                                    name="items[{{ $index }}][credit_reason_details]"
                                    rows="2"
                                    class="w-full rounded-lg border-gray-300 text-sm"
                                    placeholder="Details about this item credit..."
                                ></textarea>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('user.credit-notes.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-50">
                Cancel
            </a>
            <x-button type="submit" variant="primary">Create Credit Note</x-button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function toggleItemRow(index) {
        const checkbox = document.getElementById('item_' + index);
        const details = document.getElementById('item_details_' + index);
        const inputs = details.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            input.disabled = !checkbox.checked;
            if (!checkbox.checked) {
                input.required = false;
            } else {
                if (input.name.includes('[quantity]')) {
                    input.required = true;
                }
            }
        });
    }
</script>
@endpush
@endsection

