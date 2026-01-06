@extends('layouts.user')

@section('title', 'Invoice Report')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Invoice Report</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Analyze invoices by status, client, and date range</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('user.reports.export.invoices-csv', $filters) }}" class="px-4 py-2 bg-gray-200 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300">
                Export CSV
            </a>
            <a href="{{ route('user.reports.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300">
                Back to Reports
            </a>
        </div>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('user.reports.invoices') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $filters['start_date'] ?? '' }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $filters['end_date'] ?? '' }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Status</label>
                <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ ($filters['status'] ?? '') === 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="overdue" {{ ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <label for="client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Client</label>
                <select name="client_id" id="client_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Clients</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ ($filters['client_id'] ?? '') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Apply Filters</button>
                <a href="{{ route('user.reports.invoices') }}" class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300">Clear</a>
            </div>
        </form>
    </x-card>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <x-card>
            <p class="text-sm text-gray-500 mb-1">Total Invoices</p>
            <p class="text-2xl font-bold text-gray-900">{{ $report['summary']['total_count'] }}</p>
        </x-card>
        <x-card>
            <p class="text-sm text-gray-500 mb-1">Total Amount</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($report['summary']['total_amount'], 2) }}</p>
        </x-card>
        <x-card>
            <p class="text-sm text-gray-500 mb-1">Paid Amount</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($report['summary']['paid_amount'], 2) }}</p>
        </x-card>
        <x-card>
            <p class="text-sm text-gray-500 mb-1">Outstanding</p>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($report['summary']['outstanding_amount'], 2) }}</p>
        </x-card>
    </div>

    <!-- Status Breakdown -->
    @if(count($report['status_breakdown']) > 0)
    <x-card>
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Status Breakdown</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($report['status_breakdown'] as $status => $data)
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500 mb-1">{{ ucfirst($status) }}</p>
                    <p class="text-xl font-bold text-gray-900">{{ $data['count'] }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ number_format($data['total'], 2) }}</p>
                </div>
            @endforeach
        </div>
    </x-card>
    @endif

    <!-- Invoice List -->
    <x-card padding="none">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Invoices</h2>
        </div>
        @if($report['invoices']->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issue Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($report['invoices'] as $invoice)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('user.invoices.show', $invoice) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                        {{ $invoice->invoice_number ?? $invoice->invoice_reference }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $invoice->client->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ $invoice->issue_date?->format('M d, Y') ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-badge :variant="match($invoice->status) {
                                        'paid' => 'success',
                                        'sent' => 'info',
                                        'draft' => 'default',
                                        'overdue' => 'danger',
                                        default => 'default'
                                    }">{{ ucfirst($invoice->status) }}</x-badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ number_format($invoice->grand_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <p class="text-sm text-gray-500">No invoices found for the selected filters</p>
            </div>
        @endif
    </x-card>
</div>
@endsection

