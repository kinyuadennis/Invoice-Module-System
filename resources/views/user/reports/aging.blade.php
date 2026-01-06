@extends('layouts.user')

@section('title', 'Aging Report')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Aging Report</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Outstanding invoices grouped by age</p>
        </div>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('user.reports.aging') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">As of Date</label>
                <input 
                    type="date" 
                    name="as_of_date" 
                    value="{{ request('as_of_date', $report['as_of_date'] ?? now()->toDateString()) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                />
            </div>
            <div class="flex items-end">
                <x-button type="submit" variant="primary" class="w-full">Generate Report</x-button>
            </div>
        </form>
    </x-card>

    <!-- Summary Cards -->
    @if(isset($report['summary']))
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Total Outstanding</p>
                        <p class="text-2xl font-bold text-gray-900">KES {{ number_format($report['summary']['total_outstanding'], 2) }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </x-card>
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Total Invoices</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $report['summary']['total_invoices'] }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </x-card>
        </div>
    @endif

    <!-- Aging Buckets -->
    @if(isset($report['aging_buckets']) && count($report['aging_buckets']) > 0)
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Aging Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @foreach($report['aging_buckets'] as $bucket)
                    <div class="border border-gray-200 rounded-lg p-4 {{ $bucket['min'] > 90 ? 'bg-red-50 border-red-200' : ($bucket['min'] > 60 ? 'bg-orange-50 border-orange-200' : ($bucket['min'] > 30 ? 'bg-yellow-50 border-yellow-200' : 'bg-green-50 border-green-200')) }}">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">{{ $bucket['label'] }}</p>
                        <p class="text-2xl font-bold text-gray-900">KES {{ number_format($bucket['amount'], 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $bucket['count'] }} invoice(s)</p>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif

    <!-- Aging Details Table -->
    @if(isset($report['aging_details']) && count($report['aging_details']) > 0)
        <x-card padding="none">
            <div class="px-5 py-3 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Outstanding Invoices</h2>
            </div>
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Invoice Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Invoice Total</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount Paid</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Outstanding</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Days Overdue</th>
                    </tr>
                </x-slot>
                @foreach($report['aging_details'] as $detail)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-5 py-3 whitespace-nowrap">
                            <a href="{{ route('user.invoices.show', $detail['invoice_id']) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                {{ $detail['invoice_number'] }}
                            </a>
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $detail['client_name'] }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            {{ \Carbon\Carbon::parse($detail['invoice_date'])->format('M d, Y') }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            {{ \Carbon\Carbon::parse($detail['due_date'])->format('M d, Y') }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-300">
                            KES {{ number_format($detail['invoice_total'], 2) }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-300">
                            KES {{ number_format($detail['amount_paid'], 2) }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                            KES {{ number_format($detail['outstanding'], 2) }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right">
                            @if($detail['days_overdue'] > 0)
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $detail['days_overdue'] > 90 ? 'bg-red-100 text-red-800' : ($detail['days_overdue'] > 60 ? 'bg-orange-100 text-orange-800' : ($detail['days_overdue'] > 30 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800')) }}">
                                    {{ $detail['days_overdue'] }} days
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                    Current
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>
    @else
        <x-card>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No outstanding invoices</h3>
                <p class="mt-1 text-sm text-gray-500">All invoices are paid or there are no invoices in the selected period.</p>
            </div>
        </x-card>
    @endif
</div>
@endsection

