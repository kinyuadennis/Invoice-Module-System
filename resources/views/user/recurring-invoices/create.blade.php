@extends('layouts.user')

@section('title', 'Create Recurring Invoice')

@section('content')
<div class="space-y-6" x-data="recurringInvoiceForm()">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Create Recurring Invoice</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Set up automated recurring invoices for regular clients</p>
    </div>

    <form method="POST" action="{{ route('user.recurring-invoices.store') }}" @submit.prevent="submitForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <x-card>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Template Name *</label>
                            <input type="text" id="name" name="name" required value="{{ old('name') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g., Monthly Retainer - Client ABC">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Description</label>
                            <textarea id="description" name="description" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                        </div>

                        <div>
                            <label for="client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Client *</label>
                            <select id="client_id" name="client_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select a client</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </x-card>

                <!-- Schedule Settings -->
                <x-card>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Schedule Settings</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="frequency" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Frequency *</label>
                            <select id="frequency" name="frequency" required x-model="frequency" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly" selected>Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>

                        <div>
                            <label for="interval" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Every (Interval) *</label>
                            <input type="number" id="interval" name="interval" required min="1" max="12" value="{{ old('interval', 1) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">e.g., Every 2 months = interval of 2</p>
                        </div>

                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Start Date *</label>
                            <input type="date" id="start_date" name="start_date" required value="{{ old('start_date', now()->toDateString()) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">End Date (Optional)</label>
                            <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Leave empty for indefinite</p>
                        </div>

                        <div>
                            <label for="max_occurrences" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Max Occurrences (Optional)</label>
                            <input type="number" id="max_occurrences" name="max_occurrences" min="1" value="{{ old('max_occurrences') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Stop after generating this many invoices</p>
                        </div>
                    </div>
                </x-card>

                <!-- Invoice Template -->
                <x-card>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Invoice Template</h2>
                    
                    <!-- Line Items -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Line Items *</label>
                        <div class="space-y-3" x-ref="lineItems">
                            <template x-for="(item, index) in lineItems" :key="index">
                                <div class="grid grid-cols-12 gap-2 items-start p-3 bg-gray-50 rounded-lg">
                                    <div class="col-span-5">
                                        <input type="text" x-model="item.description" :name="`invoice_data[line_items][${index}][description]`" placeholder="Item description" required class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div class="col-span-2">
                                        <input type="number" x-model="item.quantity" :name="`invoice_data[line_items][${index}][quantity]`" placeholder="Qty" min="0" step="0.01" required class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div class="col-span-2">
                                        <input type="number" x-model="item.unit_price" :name="`invoice_data[line_items][${index}][unit_price]`" placeholder="Price" min="0" step="0.01" required class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div class="col-span-2">
                                        <input type="number" x-model="item.tax_rate" :name="`invoice_data[line_items][${index}][tax_rate]`" placeholder="Tax %" min="0" max="100" step="0.01" value="0" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div class="col-span-1">
                                        <button type="button" @click="removeLineItem(index)" class="text-red-600 hover:text-red-800">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="addLineItem()" class="mt-2 px-3 py-1.5 text-sm bg-gray-100 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-200">
                            + Add Line Item
                        </button>
                    </div>

                    <!-- Payment Terms -->
                    <div class="mb-4">
                        <label for="payment_terms" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Payment Terms (Days)</label>
                        <input type="number" id="payment_terms" name="invoice_data[payment_terms]" min="1" value="{{ old('invoice_data.payment_terms', 30) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Number of days until payment is due</p>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Notes</label>
                        <textarea id="notes" name="invoice_data[notes]" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('invoice_data.notes') }}</textarea>
                    </div>

                    <!-- Terms & Conditions -->
                    <div>
                        <label for="terms_and_conditions" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Terms & Conditions</label>
                        <textarea id="terms_and_conditions" name="invoice_data[terms_and_conditions]" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('invoice_data.terms_and_conditions') }}</textarea>
                    </div>
                </x-card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Settings -->
                <x-card>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Settings</h2>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="auto_send" name="auto_send" value="1" {{ old('auto_send') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="auto_send" class="ml-2 text-sm text-gray-700 dark:text-gray-200">Auto-send when generated</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="send_reminders" name="send_reminders" value="1" {{ old('send_reminders', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="send_reminders" class="ml-2 text-sm text-gray-700 dark:text-gray-200">Send payment reminders</label>
                        </div>
                    </div>
                </x-card>

                <!-- Actions -->
                <div class="flex flex-col gap-2">
                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Create Recurring Invoice
                    </button>
                    <a href="{{ route('user.recurring-invoices.index') }}" class="w-full px-4 py-2 bg-gray-200 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 text-center">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function recurringInvoiceForm() {
    return {
        frequency: 'monthly',
        lineItems: [
            { description: '', quantity: 1, unit_price: 0, tax_rate: 0 }
        ],
        addLineItem() {
            this.lineItems.push({ description: '', quantity: 1, unit_price: 0, tax_rate: 0 });
        },
        removeLineItem(index) {
            if (this.lineItems.length > 1) {
                this.lineItems.splice(index, 1);
            }
        },
        submitForm(event) {
            // Validate at least one line item has description
            const hasValidItem = this.lineItems.some(item => item.description.trim() !== '');
            if (!hasValidItem) {
                alert('Please add at least one line item with a description.');
                event.preventDefault();
                return;
            }
            event.target.submit();
        }
    }
}
</script>
@endpush
@endsection

