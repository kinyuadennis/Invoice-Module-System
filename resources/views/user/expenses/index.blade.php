@extends('layouts.user')

@section('title', 'Expenses')

@section('content')
<div class="space-y-6 mb-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Expenses</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Track and manage all your business expenses</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('user.expenses.create') }}">
                <x-button variant="primary">
                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Expense
                </x-button>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    @if(isset($stats))
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Total Expenses</p>
                        <p class="text-2xl font-bold text-gray-900">KES {{ number_format($stats['total_amount'] ?? 0, 2) }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </x-card>
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Total Count</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </x-card>
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Pending</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </x-card>
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Tax Deductible</p>
                        <p class="text-2xl font-bold text-gray-900">KES {{ number_format($stats['tax_deductible_total'] ?? 0, 2) }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </x-card>
        </div>
    @endif

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('user.expenses.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-5">
            <div>
                <x-input 
                    type="text" 
                    name="search" 
                    label="Search" 
                    value="{{ request('search') }}"
                    placeholder="Description, vendor, client..."
                />
            </div>
            
            <div>
                <x-select 
                    name="status" 
                    label="Status"
                    :options="[
                        ['value' => '', 'label' => 'All Statuses'],
                        ['value' => 'pending', 'label' => 'Pending'],
                        ['value' => 'approved', 'label' => 'Approved'],
                        ['value' => 'rejected', 'label' => 'Rejected'],
                        ['value' => 'paid', 'label' => 'Paid'],
                    ]"
                    value="{{ request('status') }}"
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

            <div>
                <x-select 
                    name="dateRange" 
                    label="Date Range"
                    :options="[
                        ['value' => '', 'label' => 'All Time'],
                        ['value' => 'today', 'label' => 'Today'],
                        ['value' => 'week', 'label' => 'This Week'],
                        ['value' => 'month', 'label' => 'This Month'],
                        ['value' => 'quarter', 'label' => 'This Quarter'],
                        ['value' => 'year', 'label' => 'This Year'],
                    ]"
                    value="{{ request('dateRange') }}"
                />
            </div>

            <div class="flex items-end">
                <x-button type="submit" variant="primary" class="w-full">Filter</x-button>
            </div>
        </form>
    </x-card>

    <!-- Expenses Table -->
    @if(isset($expenses) && $expenses->count() > 0)
        <x-card padding="none">
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </x-slot>
                @foreach($expenses as $expense)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            {{ \Carbon\Carbon::parse($expense['expense_date'])->format('M d, Y') }}
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-900">
                            <div class="flex items-center gap-2">
                                {{ $expense['description'] }}
                                @if($expense['has_receipt'] ?? false)
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Has receipt">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap">
                            @if(isset($expense['category']))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: {{ $expense['category']['color'] }}20; color: {{ $expense['category']['color'] }}">
                                    {{ $expense['category']['name'] }}
                                </span>
                            @else
                                <span class="text-sm text-gray-500">Uncategorized</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            {{ $expense['client']['name'] ?? '-' }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap">
                            @php
                                $statusVariant = match(strtolower($expense['status'] ?? 'pending')) {
                                    'approved', 'paid' => 'success',
                                    'rejected' => 'danger',
                                    default => 'warning'
                                };
                            @endphp
                            <x-badge :variant="$statusVariant">{{ ucfirst($expense['status'] ?? 'pending') }}</x-badge>
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                            KES {{ number_format($expense['amount'], 2) }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('user.expenses.show', $expense['id']) }}" class="text-indigo-600 hover:text-indigo-900" title="View">View</a>
                                <a href="{{ route('user.expenses.edit', $expense['id']) }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900" title="Edit">Edit</a>
                                <form method="POST" action="{{ route('user.expenses.destroy', $expense['id']) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <!-- Pagination -->
            @if(method_exists($expenses, 'links'))
                <div class="px-5 py-3 border-t border-gray-200">
                    {{ $expenses->links() }}
                </div>
            @endif
        </x-card>
    @else
        <x-card>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No expenses</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by recording your first expense.</p>
                <div class="mt-6">
                    <a href="{{ route('user.expenses.create') }}">
                        <x-button variant="primary">New Expense</x-button>
                    </a>
                </div>
            </div>
        </x-card>
    @endif
</div>
@endsection

