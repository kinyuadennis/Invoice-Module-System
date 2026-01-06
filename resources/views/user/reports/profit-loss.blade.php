@extends('layouts.user')

@section('title', 'Profit & Loss Statement')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Profit & Loss Statement</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Revenue, expenses, and profit analysis</p>
        </div>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('user.reports.profit-loss') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Start Date</label>
                <input 
                    type="date" 
                    name="start_date" 
                    value="{{ request('start_date', $report['period']['start'] ?? now()->startOfYear()->toDateString()) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">End Date</label>
                <input 
                    type="date" 
                    name="end_date" 
                    value="{{ request('end_date', $report['period']['end'] ?? now()->endOfYear()->toDateString()) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                />
            </div>
            <div class="flex items-end">
                <x-button type="submit" variant="primary" class="w-full">Generate Report</x-button>
            </div>
        </form>
    </x-card>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-card padding="sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Total Revenue</p>
                    <p class="text-2xl font-bold text-green-600">KES {{ number_format($report['revenue']['total_revenue'] ?? 0, 2) }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </x-card>
        <x-card padding="sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Total Expenses</p>
                    <p class="text-2xl font-bold text-red-600">KES {{ number_format($report['expenses']['total_expenses'] ?? 0, 2) }}</p>
                </div>
                <div class="p-3 bg-red-100 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </x-card>
        <x-card padding="sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Gross Profit</p>
                    <p class="text-2xl font-bold {{ ($report['profit_loss']['gross_profit'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        KES {{ number_format($report['profit_loss']['gross_profit'] ?? 0, 2) }}
                    </p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
        </x-card>
        <x-card padding="sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Profit Margin</p>
                    <p class="text-2xl font-bold {{ ($report['profit_loss']['profit_margin'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($report['profit_loss']['profit_margin'] ?? 0, 2) }}%
                    </p>
                </div>
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Revenue Breakdown -->
    <x-card>
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Revenue Breakdown</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="border border-gray-200 rounded-lg p-4">
                <p class="text-sm text-gray-600 dark:text-gray-300">Total Revenue</p>
                <p class="text-2xl font-bold text-gray-900">KES {{ number_format($report['revenue']['total_revenue'] ?? 0, 2) }}</p>
            </div>
            <div class="border border-gray-200 rounded-lg p-4">
                <p class="text-sm text-gray-600 dark:text-gray-300">VAT Collected</p>
                <p class="text-2xl font-bold text-gray-900">KES {{ number_format($report['revenue']['vat_collected'] ?? 0, 2) }}</p>
            </div>
            <div class="border border-gray-200 rounded-lg p-4">
                <p class="text-sm text-gray-600 dark:text-gray-300">Platform Fees</p>
                <p class="text-2xl font-bold text-gray-900">KES {{ number_format($report['revenue']['platform_fees'] ?? 0, 2) }}</p>
            </div>
            <div class="border border-gray-200 rounded-lg p-4 bg-blue-50">
                <p class="text-sm text-gray-600 dark:text-gray-300">Net Revenue</p>
                <p class="text-2xl font-bold text-blue-900">KES {{ number_format($report['revenue']['net_revenue'] ?? 0, 2) }}</p>
            </div>
        </div>
    </x-card>

    <!-- Expenses Breakdown -->
    <x-card>
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Expenses Breakdown</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="border border-gray-200 rounded-lg p-4">
                <p class="text-sm text-gray-600 dark:text-gray-300">Total Expenses</p>
                <p class="text-2xl font-bold text-gray-900">KES {{ number_format($report['expenses']['total_expenses'] ?? 0, 2) }}</p>
            </div>
            <div class="border border-gray-200 rounded-lg p-4">
                <p class="text-sm text-gray-600 dark:text-gray-300">Tax Deductible</p>
                <p class="text-2xl font-bold text-green-600">KES {{ number_format($report['expenses']['tax_deductible'] ?? 0, 2) }}</p>
            </div>
            <div class="border border-gray-200 rounded-lg p-4">
                <p class="text-sm text-gray-600 dark:text-gray-300">Non-Tax Deductible</p>
                <p class="text-2xl font-bold text-gray-900">KES {{ number_format($report['expenses']['non_tax_deductible'] ?? 0, 2) }}</p>
            </div>
        </div>

        <!-- Expenses by Category -->
        @if(isset($report['expenses']['by_category']) && count($report['expenses']['by_category']) > 0)
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Expenses by Category</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($report['expenses']['by_category'] as $category)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $category['category_name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                        KES {{ number_format($category['amount'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-300">
                                        @if(($report['expenses']['total_expenses'] ?? 0) > 0)
                                            {{ number_format(($category['amount'] / $report['expenses']['total_expenses']) * 100, 1) }}%
                                        @else
                                            0%
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </x-card>

    <!-- Monthly Breakdown -->
    @if(isset($report['monthly_breakdown']) && count($report['monthly_breakdown']) > 0)
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Monthly Breakdown</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Expenses</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Profit/Loss</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($report['monthly_breakdown'] as $month)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $month['month'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-green-600">
                                    KES {{ number_format($month['revenue'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-red-600">
                                    KES {{ number_format($month['expenses'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium {{ $month['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    KES {{ number_format($month['profit'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif
</div>
@endsection

