@extends('layouts.user')

@section('title', 'Expense Breakdown Report')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Expense Breakdown</h1>
            <p class="mt-1 text-sm text-gray-600">Detailed expense analysis and categorization</p>
        </div>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('user.reports.expenses') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input 
                    type="date" 
                    name="start_date" 
                    value="{{ request('start_date', $report['period']['start'] ?? now()->startOfMonth()->toDateString()) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input 
                    type="date" 
                    name="end_date" 
                    value="{{ request('end_date', $report['period']['end'] ?? now()->endOfMonth()->toDateString()) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                />
            </div>
            <div>
                <x-select 
                    name="category_id" 
                    label="Category"
                    :options="array_merge([['value' => '', 'label' => 'All Categories']], $categories->map(function($c) { return ['value' => $c->id, 'label' => $c->name]; })->toArray())"
                    value="{{ request('category_id') }}"
                />
            </div>
            <div class="flex items-end">
                <x-button type="submit" variant="primary" class="w-full">Generate Report</x-button>
            </div>
        </form>
    </x-card>

    <!-- Summary Cards -->
    @if(isset($report['summary']))
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Expenses</p>
                        <p class="text-2xl font-bold text-gray-900">KES {{ number_format($report['summary']['total_expenses'], 2) }}</p>
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
                        <p class="text-sm text-gray-600">Tax Deductible</p>
                        <p class="text-2xl font-bold text-green-600">KES {{ number_format($report['summary']['tax_deductible'], 2) }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </x-card>
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Expense Count</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $report['summary']['expense_count'] }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </x-card>
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Average Expense</p>
                        <p class="text-2xl font-bold text-gray-900">KES {{ number_format($report['summary']['average_expense'], 2) }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
            </x-card>
        </div>
    @endif

    <!-- Expenses by Category -->
    @if(isset($report['by_category']) && count($report['by_category']) > 0)
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Expenses by Category</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tax Deductible</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($report['by_category'] as $category)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $category['category_name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                    KES {{ number_format($category['amount'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-green-600">
                                    KES {{ number_format($category['tax_deductible'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                    {{ $category['count'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                    @if(($report['summary']['total_expenses'] ?? 0) > 0)
                                        {{ number_format(($category['amount'] / $report['summary']['total_expenses']) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif

    <!-- Expenses by Payment Method -->
    @if(isset($report['by_payment_method']) && count($report['by_payment_method']) > 0)
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Expenses by Payment Method</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($report['by_payment_method'] as $method)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">{{ ucfirst($method['method']) }}</p>
                        <p class="text-2xl font-bold text-gray-900">KES {{ number_format($method['amount'], 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $method['count'] }} expense(s)</p>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif

    <!-- Monthly Breakdown -->
    @if(isset($report['monthly_breakdown']) && count($report['monthly_breakdown']) > 0)
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Monthly Breakdown</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Average</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($report['monthly_breakdown'] as $month)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $month['month'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                    KES {{ number_format($month['amount'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                    {{ $month['count'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                    KES {{ number_format($month['count'] > 0 ? $month['amount'] / $month['count'] : 0, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif

    <!-- Top Expenses -->
    @if(isset($report['top_expenses']) && count($report['top_expenses']) > 0)
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Top 10 Expenses</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expense #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($report['top_expenses'] as $expense)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('user.expenses.show', $expense['id']) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                        {{ $expense['expense_number'] }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $expense['description'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $expense['category'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                    KES {{ number_format($expense['amount'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ \Carbon\Carbon::parse($expense['date'])->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-badge :variant="match($expense['status']) { 'paid' => 'success', 'pending' => 'warning', default => 'default' }">
                                        {{ ucfirst($expense['status']) }}
                                    </x-badge>
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

