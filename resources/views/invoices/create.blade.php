@extends('layouts.app')

@section('title', 'Create Invoice')

@section('content')
<div class="space-y-6" x-data="invoiceForm()">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Create Invoice</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Fill out the details below to create a new invoice</p>
    </div>

    <form method="POST" action="{{ route('invoices.store') }}" @submit.prevent="submitForm">
        @csrf

        <!-- Invoice Header -->
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Invoice Details</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <x-select 
                    name="client_id" 
                    label="Client"
                    :options="collect($clients ?? [])->map(fn($client) => ['value' => $client['id'], 'label' => $client['name']])->prepend(['value' => '', 'label' => 'Select a client'])->toArray()"
                    required
                />

                <x-input 
                    type="date" 
                    name="issue_date" 
                    label="Issue Date" 
                    value="{{ old('issue_date', date('Y-m-d')) }}"
                    required
                />

                <x-input 
                    type="date" 
                    name="due_date" 
                    label="Due Date" 
                    value="{{ old('due_date') }}"
                    required
                />

                <x-select 
                    name="status" 
                    label="Status"
                    :options="[
                        ['value' => 'draft', 'label' => 'Draft'],
                        ['value' => 'sent', 'label' => 'Sent'],
                    ]"
                    value="{{ old('status', 'draft') }}"
                />
            </div>
        </x-card>

        <!-- Line Items -->
        <x-card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Line Items</h2>
                <x-button type="button" variant="secondary" size="sm" @click="addItem">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Item
                </x-button>
            </div>

            <div class="space-y-4">
                <template x-for="(item, index) in items" :key="index">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-12 items-end">
                        <div class="sm:col-span-5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1" x-show="index === 0">Description</label>
                            <input 
                                type="text" 
                                x-model="item.description"
                                :name="`items[${index}][description]`"
                                placeholder="Item description"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                required
                            >
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1" x-show="index === 0">Quantity</label>
                            <input 
                                type="number" 
                                x-model.number="item.quantity"
                                :name="`items[${index}][quantity]`"
                                min="1"
                                step="1"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                required
                            >
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1" x-show="index === 0">Rate</label>
                            <input 
                                type="number" 
                                x-model.number="item.rate"
                                :name="`items[${index}][rate]`"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                required
                            >
                        </div>
                        <div class="sm:col-span-1 text-right">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1" x-show="index === 0">Amount</label>
                            <p class="text-base font-medium text-gray-900 mt-2 sm:mt-0" x-text="formatCurrency(item.quantity * item.rate)"></p>
                        </div>
                        <div class="sm:col-span-1 flex justify-end">
                            <x-button 
                                type="button" 
                                variant="ghost" 
                                size="sm" 
                                @click="removeItem(index)"
                                :disabled="items.length === 1"
                            >
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </x-button>
                        </div>
                    </div>
                </template>
            </div>

            @error('items')
                <p class="mt-4 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </x-card>

        <!-- Totals & Notes -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <x-card>
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Notes</h2>
                <textarea 
                    name="notes" 
                    rows="4"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="Any additional notes or terms..."
                >{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </x-card>

            <x-card>
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Summary</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Subtotal</span>
                        <span class="font-medium text-gray-900" x-text="formatCurrency(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Tax (<span x-text="taxRate"></span>%)</span>
                        <span class="font-medium text-gray-900" x-text="formatCurrency(tax)"></span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-gray-200 text-base font-semibold text-gray-900">
                        <span>Total</span>
                        <span class="text-indigo-600" x-text="formatCurrency(total)"></span>
                    </div>
                </div>
                <div class="mt-6">
                    <x-button type="submit" variant="primary" class="w-full">
                        Save Invoice
                    </x-button>
                </div>
            </x-card>
        </div>
    </form>
</div>

<script>
function invoiceForm() {
    return {
        items: @json(old('items', [['description' => '', 'quantity' => 1, 'rate' => 0]])),
        taxRate: {{ old('tax_rate', 8) }},
        
        addItem() {
            this.items.push({ description: '', quantity: 1, rate: 0 });
        },
        
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        
        get subtotal() {
            return this.items.reduce((sum, item) => {
                return sum + (item.quantity || 0) * (item.rate || 0);
            }, 0);
        },
        
        get tax() {
            return (this.subtotal * this.taxRate) / 100;
        },
        
        get total() {
            return this.subtotal + this.tax;
        },
        
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2
            }).format(amount);
        },
        
        submitForm(event) {
            // Add hidden inputs for items
            const form = event.target;
            this.items.forEach((item, index) => {
                const descInput = document.createElement('input');
                descInput.type = 'hidden';
                descInput.name = `items[${index}][description]`;
                descInput.value = item.description;
                form.appendChild(descInput);
                
                const qtyInput = document.createElement('input');
                qtyInput.type = 'hidden';
                qtyInput.name = `items[${index}][quantity]`;
                qtyInput.value = item.quantity;
                form.appendChild(qtyInput);
                
                const rateInput = document.createElement('input');
                rateInput.type = 'hidden';
                rateInput.name = `items[${index}][rate]`;
                rateInput.value = item.rate;
                form.appendChild(rateInput);
            });
            
            form.submit();
        }
    }
}
</script>
@endsection

