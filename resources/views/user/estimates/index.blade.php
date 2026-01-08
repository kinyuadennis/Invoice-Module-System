@extends('layouts.user')

@section('title', 'Estimates')

@section('content')
<div class="space-y-6 mb-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Estimates</h1>
            <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Manage and track all your estimates and quotes</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('user.estimates.create') }}">
                <x-button variant="primary">
                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Estimate
                </x-button>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('user.estimates.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
                <x-input
                    type="text"
                    name="search"
                    label="Search"
                    value="{{ request('search') }}"
                    placeholder="Estimate #, client name..." />
            </div>

            <div>
                <x-select
                    name="status"
                    label="Status"
                    :options="[
                        ['value' => '', 'label' => 'All Statuses'],
                        ['value' => 'draft', 'label' => 'Draft'],
                        ['value' => 'sent', 'label' => 'Sent'],
                        ['value' => 'accepted', 'label' => 'Accepted'],
                        ['value' => 'rejected', 'label' => 'Rejected'],
                        ['value' => 'expired', 'label' => 'Expired'],
                        ['value' => 'converted', 'label' => 'Converted'],
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

    <!-- Estimates Table -->
    @if(isset($estimates) && $estimates->count() > 0)

    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Estimate #</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Client</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Issue Date</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Expiry Date</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Amount</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#2A2A2A]">
                    @foreach($estimates as $estimate)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('user.estimates.show', $estimate['id']) }}" class="text-sm font-bold text-[#2B6EF6] hover:text-[#2563EB] transition-colors duration-150">
                                {{ $estimate['estimate_number'] ?? 'EST-' . str_pad($estimate['id'], 3, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            {{ $estimate['client']['name'] ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                            $statusVariant = match(strtolower($estimate['status'] ?? 'draft')) {
                            'accepted' => 'success',
                            'sent' => 'info',
                            'rejected' => 'danger',
                            'expired' => 'warning',
                            'converted' => 'success',
                            default => 'default'
                            };
                            $statusColor = match(strtolower($estimate['status'] ?? 'draft')) {
                            'accepted', 'converted' => 'text-emerald-600 bg-emerald-500/10 border-emerald-500/20',
                            'sent' => 'text-blue-600 bg-blue-500/10 border-blue-500/20',
                            'rejected' => 'text-red-600 bg-red-500/10 border-red-500/20',
                            'expired' => 'text-amber-600 bg-amber-500/10 border-amber-500/20',
                            default => 'text-gray-600 bg-gray-500/10 border-gray-500/20'
                            };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest border {{ $statusColor }}">
                                {{ ucfirst($estimate['status'] ?? 'draft') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            {{ $estimate['issue_date'] ? \Carbon\Carbon::parse($estimate['issue_date'])->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            {{ $estimate['expiry_date'] ? \Carbon\Carbon::parse($estimate['expiry_date'])->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-gray-900 dark:text-white">
                            KES {{ number_format($estimate['grand_total'] ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('user.estimates.show', $estimate['id']) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs uppercase tracking-wider" title="View">View</a>
                                @if(($estimate['status'] ?? 'draft') === 'draft')
                                <a href="{{ route('user.estimates.edit', $estimate['id']) }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 font-bold text-xs uppercase tracking-wider" title="Edit">Edit</a>
                                @endif
                                @if(($estimate['status'] ?? 'draft') === 'draft')
                                <form method="POST" action="{{ route('user.estimates.destroy', $estimate['id']) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this estimate?');">
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
        @if(method_exists($estimates, 'links'))
        <div class="px-6 py-4 border-t border-gray-200 dark:border-[#333333]">
            {{ $estimates->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-[#9A9A9A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No estimates</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-[#9A9A9A]">Get started by creating a new estimate.</p>
        <div class="mt-6">
            <a href="{{ route('user.estimates.create') }}">
                <x-button variant="primary">New Estimate</x-button>
            </a>
        </div>
    </div>
    @endif
</div>
@endsection