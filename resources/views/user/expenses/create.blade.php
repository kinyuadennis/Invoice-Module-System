@extends('layouts.user')

@section('title', 'Create Expense')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Record New Expense</h1>
        <p class="mt-1 text-sm text-gray-600">Track your business expenses</p>
    </div>

    <form method="POST" action="{{ route('user.expenses.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Expense Details -->
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Expense Details</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <x-select 
                        name="category_id" 
                        label="Category"
                        :options="array_merge([['value' => '', 'label' => 'Select Category']], $categories->map(function($c) { return ['value' => $c->id, 'label' => $c->name]; })->toArray())"
                        value="{{ old('category_id') }}"
                    />
                </div>

                <div>
                    <x-input 
                        type="date" 
                        name="expense_date" 
                        label="Expense Date *"
                        value="{{ old('expense_date', now()->toDateString()) }}"
                        required
                    />
                </div>

                <div class="sm:col-span-2">
                    <x-input 
                        type="text" 
                        name="description" 
                        label="Description *"
                        value="{{ old('description') }}"
                        placeholder="e.g., Office supplies, Travel expenses"
                        required
                    />
                </div>

                <div>
                    <x-input 
                        type="number" 
                        name="amount" 
                        label="Amount (KES) *"
                        value="{{ old('amount') }}"
                        step="0.01"
                        min="0.01"
                        placeholder="0.00"
                        required
                    />
                </div>

                <div>
                    <x-select 
                        name="payment_method" 
                        label="Payment Method"
                        :options="[
                            ['value' => '', 'label' => 'Select Method'],
                            ['value' => 'cash', 'label' => 'Cash'],
                            ['value' => 'mpesa', 'label' => 'M-PESA'],
                            ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
                            ['value' => 'card', 'label' => 'Card'],
                            ['value' => 'other', 'label' => 'Other'],
                        ]"
                        value="{{ old('payment_method') }}"
                    />
                </div>

                <div>
                    <x-input 
                        type="text" 
                        name="vendor_name" 
                        label="Vendor Name"
                        value="{{ old('vendor_name') }}"
                        placeholder="Who was paid?"
                    />
                </div>

                <div>
                    <x-input 
                        type="text" 
                        name="reference_number" 
                        label="Reference Number"
                        value="{{ old('reference_number') }}"
                        placeholder="Payment reference"
                    />
                </div>

                <div>
                    <x-select 
                        name="client_id" 
                        label="Link to Client (Optional)"
                        :options="array_merge([['value' => '', 'label' => 'No Client']], $clients->map(function($c) { return ['value' => $c->id, 'label' => $c->name]; })->toArray())"
                        value="{{ old('client_id') }}"
                    />
                </div>

                <div class="sm:col-span-2">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="tax_deductible" 
                            id="tax_deductible"
                            value="1"
                            {{ old('tax_deductible', true) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <label for="tax_deductible" class="ml-2 text-sm text-gray-700">
                            Tax Deductible
                        </label>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Receipt (Optional)
                    </label>
                    <input 
                        type="file" 
                        name="receipt" 
                        accept="image/*,.pdf"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    >
                    <p class="mt-1 text-xs text-gray-500">Upload receipt image or PDF (max 10MB)</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea 
                        name="notes" 
                        rows="3"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Additional notes about this expense..."
                    >{{ old('notes') }}</textarea>
                </div>
            </div>
        </x-card>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('user.expenses.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <x-button type="submit" variant="primary">Create Expense</x-button>
        </div>
    </form>
</div>
@endsection

