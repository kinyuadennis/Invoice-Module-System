@extends('layouts.app')

@section('title', 'Record Payment')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Record Payment</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Record a manual payment for an invoice</p>
        </div>
        <a href="{{ route('user.payments.index') }}">
            <x-button variant="secondary">Back to Payments</x-button>
        </a>
    </div>

    <x-card>
        <form method="POST" action="{{ route('user.invoices.record-payment', ['id' => request('invoice_id', 0)]) }}" id="payment-form">
            @csrf

            <div class="space-y-6">
                <!-- Invoice Selection -->
                <div x-data="{ 
                    selectedInvoice: '{{ request('invoice_id', '') }}',
                    invoices: {{ \App\Models\Invoice::where('company_id', \App\Services\CurrentCompanyService::requireId())->whereIn('status', ['sent', 'partial'])->get()->map(function($i) { return ['id' => $i->id, 'reference' => $i->invoice_reference ?? $i->invoice_number, 'amount_due' => $i->amount_due, 'client_name' => $i->client->name ?? 'Unknown']; }) }}
                }">
                    <label for="invoice_id" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Select Invoice (Sent or Partial)</label>
                    <select id="invoice_id" name="invoice_id" x-model="selectedInvoice" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">Select an invoice...</option>
                        <template x-for="invoice in invoices" :key="invoice.id">
                            <option :value="invoice.id" x-text="`${invoice.reference} - ${invoice.client_name} (Due: ${invoice.amount_due})`"></option>
                        </template>
                    </select>
                    @error('invoice_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Update Form Action based on selection -->
                <script>
                    document.getElementById('invoice_id').addEventListener('change', function() {
                        const form = document.getElementById('payment-form');
                        // Use a placeholder ID that we'll replace, or handle in controller
                        // Actually, the record-payment route requires invoice ID in URL: /invoices/{id}/record-payment
                        // Start with a base URL and replace the ID
                        const baseUrl = "{{ url('app/invoices') }}";
                        const invoiceId = this.value;
                        if (invoiceId) {
                            form.action = `${baseUrl}/${invoiceId}/record-payment`;
                        }
                    });
                </script>

                <!-- Amount -->
                <div>
                    <x-input
                        type="number"
                        name="amount"
                        label="Payment Amount"
                        step="0.01"
                        placeholder="0.00"
                        required />
                </div>

                <!-- Payment Date -->
                <div>
                    <x-input
                        type="date"
                        name="payment_date"
                        label="Payment Date"
                        value="{{ date('Y-m-d') }}"
                        required />
                </div>

                <!-- Payment Method -->
                <div>
                    <x-select
                        name="payment_method"
                        label="Payment Method"
                        :options="[
                            ['value' => 'M-Pesa', 'label' => 'M-Pesa'],
                            ['value' => 'Bank Transfer', 'label' => 'Bank Transfer'],
                            ['value' => 'Cash', 'label' => 'Cash'],
                            ['value' => 'Cheque', 'label' => 'Cheque'],
                            ['value' => 'Other', 'label' => 'Other'],
                        ]"
                        required />
                </div>

                <!-- Reference (Optional) -->
                <div>
                    <x-input
                        type="text"
                        name="transaction_reference"
                        label="Transaction Reference (Optional)"
                        placeholder="e.g. M-Pesa Code" />
                </div>

                <!-- Notes (Optional) -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                </div>

                <div class="flex justify-end">
                    <x-button type="submit" variant="primary">Record Payment</x-button>
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection