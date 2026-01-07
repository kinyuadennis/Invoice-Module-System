@extends('layouts.user')

@section('title', 'Expenses')

@section('content')
<div class="space-y-6 mb-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Expenses</h1>
            <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Track and manage all your business expenses</p>
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
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-[#9A9A9A] uppercase tracking-wider">Total Expenses</p>
                    <p class="mt-2 text-2xl font-black text-gray-900 dark:text-white tracking-tight">KES {{ number_format($stats['total_amount'] ?? 0, 2) }}</p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-500/10 rounded-xl">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-[#9A9A9A] uppercase tracking-wider">Total Count</p>
                    <p class="mt-2 text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-emerald-100 dark:bg-emerald-500/10 rounded-xl">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-[#9A9A9A] uppercase tracking-wider">Pending</p>
                    <p class="mt-2 text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ $stats['pending'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-amber-100 dark:bg-amber-500/10 rounded-xl">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-[#9A9A9A] uppercase tracking-wider">Tax Deductible</p>
                    <p class="mt-2 text-2xl font-black text-gray-900 dark:text-white tracking-tight">KES {{ number_format($stats['tax_deductible_total'] ?? 0, 2) }}</p>
                </div>
                <div class="p-3 bg-purple-100 dark:bg-purple-500/10 rounded-xl">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>
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
                    placeholder="Description, vendor, client..." />
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
                    value="{{ request('status') }}" />
            </div>

            <div>
                <x-select
                    name="category_id"
                    label="Category"
                    :options="array_merge([['value' => '', 'label' => 'All Categories']], $categories->map(function($c) { return ['value' => $c->id, 'label' => $c->name]; })->toArray())"
                    value="{{ request('category_id') }}" />
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
                    value="{{ request('dateRange') }}" />
            </div>

            <div class="flex items-end">
                <x-button type="submit" variant="primary" class="w-full">Filter</x-button>
            </div>
        </form>
    </x-card>

    <!-- Expenses Table -->
    @if(isset($expenses) && $expenses->count() > 0)
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Date</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Description</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Category</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Client</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Amount</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#2A2A2A]">
                    @foreach($expenses as $expense)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            {{ \Carbon\Carbon::parse($expense['expense_date'])->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                            <div class="flex items-center gap-2">
                                {{ $expense['description'] }}
                                @if($expense['has_receipt'] ?? false)
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Has receipt">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if(isset($expense['category']))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest" style="background-color: {{ $expense['category']['color'] }}20; color: {{ $expense['category']['color'] }}">
                                {{ $expense['category']['name'] }}
                            </span>
                            @else
                            <span class="text-sm text-gray-500 font-medium">Uncategorized</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            {{ $expense['client']['name'] ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                            $statusVariant = match(strtolower($expense['status'] ?? 'pending')) {
                            'approved', 'paid' => 'success',
                            'rejected' => 'danger',
                            default => 'warning'
                            };
                            $statusColor = match(strtolower($expense['status'] ?? 'pending')) {
                            'approved', 'paid' => 'text-emerald-600 bg-emerald-500/10 border-emerald-500/20',
                            'rejected' => 'text-red-600 bg-red-500/10 border-red-500/20',
                            default => 'text-amber-600 bg-amber-500/10 border-amber-500/20'
                            };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest border {{ $statusColor }}">
                                {{ ucfirst($expense['status'] ?? 'pending') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-gray-900 dark:text-white">
                            KES {{ number_format($expense['amount'], 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('user.expenses.show', $expense['id']) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs uppercase tracking-wider" title="View">View</a>
                                <a href="{{ route('user.expenses.edit', $expense['id']) }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 font-bold text-xs uppercase tracking-wider" title="Edit">Edit</a>
                                <form method="POST" action="{{ route('user.expenses.destroy', $expense['id']) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-bold text-xs uppercase tracking-wider" title="Delete">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(method_exists($expenses, 'links'))
        <div class="px-6 py-4 border-t border-gray-200 dark:border-[#333333]">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-[#9A9A9A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No expenses</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-[#9A9A9A]">Get started by recording your first expense.</p>
        <div class="mt-6">
            <a href="{{ route('user.expenses.create') }}">
                <x-button variant="primary">New Expense</x-button>
            </a>
        </div>
    </div>
    @endif
</div>
@endsection