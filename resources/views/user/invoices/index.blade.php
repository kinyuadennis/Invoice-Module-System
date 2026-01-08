@extends('layouts.user')

@section('title', 'Invoices')

@section('content')
<div class="space-y-6 mb-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Invoices</h1>
            <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Manage and track all your invoices</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('user.company.invoice-customization') }}" class="inline-flex items-center px-4 py-2 text-xs font-black text-gray-700 dark:text-gray-200 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl hover:bg-gray-50 dark:hover:bg-white/10 transition-all uppercase tracking-widest">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                </svg>
                Customize
            </a>
            <a href="{{ route('user.invoices.create') }}" class="inline-flex items-center px-4 py-2 text-xs font-black text-white bg-blue-600 rounded-xl hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/20 transition-all uppercase tracking-widest">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Invoice
            </a>
        </div>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('user.invoices.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
                <x-input
                    type="text"
                    name="search"
                    label="Search"
                    value="{{ request('search') }}"
                    placeholder="Invoice #, client name..." />
            </div>

            <div>
                <x-select
                    name="status"
                    label="Status"
                    :options="[
                        ['value' => '', 'label' => 'All Statuses'],
                        ['value' => 'draft', 'label' => 'Draft'],
                        ['value' => 'sent', 'label' => 'Sent'],
                        ['value' => 'paid', 'label' => 'Paid'],
                        ['value' => 'overdue', 'label' => 'Overdue'],
                        ['value' => 'cancelled', 'label' => 'Cancelled'],
                    ]"
                    value="{{ request('status') }}" />
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

    <!-- Invoices Table -->
    <!-- Invoices Table -->
    @if(isset($invoices) && $invoices->count() > 0)
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Invoice #</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Client</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Issue Date</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Due Date</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Amount</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#2A2A2A]">
                    @foreach($invoices as $invoice)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('user.invoices.show', $invoice['id']) }}" class="text-sm font-black text-[#2B6EF6] hover:text-[#2563EB] transition-colors duration-150">
                                {{ $invoice['invoice_number'] ?? 'INV-' . str_pad($invoice['id'], 3, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ $invoice['client']['name'] ?? 'Unknown' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                            $statusClasses = match(strtolower($invoice['status'] ?? 'draft')) {
                            'paid' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 ring-emerald-500/20',
                            'sent' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 ring-blue-500/20',
                            'overdue' => 'bg-red-500/10 text-red-600 dark:text-red-400 ring-red-500/20',
                            'pending' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 ring-amber-500/20',
                            default => 'bg-gray-500/10 text-gray-600 dark:text-gray-400 ring-gray-500/20'
                            };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest ring-1 {{ $statusClasses }}">
                                {{ ucfirst($invoice['status'] ?? 'draft') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">
                            {{ $invoice['issue_date'] ? \Carbon\Carbon::parse($invoice['issue_date'])->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">
                            {{ $invoice['due_date'] ? \Carbon\Carbon::parse($invoice['due_date'])->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-gray-900 dark:text-white">
                            KES {{ number_format($invoice['total'] ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                <a href="{{ route('user.invoices.show', $invoice['id']) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs uppercase tracking-wider" title="View">View</a>
                                @if(($invoice['status'] ?? 'draft') === 'draft')
                                <a href="{{ route('user.invoices.edit', $invoice['id']) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 font-bold text-xs uppercase tracking-wider" title="Edit">Edit</a>
                                @endif
                                <form method="POST" action="{{ route('user.invoices.duplicate', $invoice['id']) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-blue-600 hover:text-blue-900 font-bold text-xs uppercase tracking-wider" title="Duplicate invoice">Duplicate</button>
                                </form>
                                @if(($invoice['status'] ?? 'draft') === 'draft')
                                <form method="POST" action="{{ route('user.invoices.destroy', $invoice['id']) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-bold text-xs uppercase tracking-wider" title="Delete">Delete</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(method_exists($invoices, 'links'))
        <div class="px-5 py-3 border-t border-gray-200">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
    @else
    <x-card>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No invoices</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new invoice.</p>
            <div class="mt-6">
                <a href="{{ route('user.invoices.create') }}">
                    <x-button variant="primary">New Invoice</x-button>
                </a>
            </div>
        </div>
    </x-card>
    @endif
</div>
@endsection