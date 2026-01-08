@extends('layouts.user')

@section('title', 'Credit Notes')

@section('content')
<div class="space-y-6 mb-6">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Credit Notes</h1>
            <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Manage credit notes and refunds</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('user.credit-notes.create') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 uppercase tracking-wider">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Credit Note
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    @if(isset($stats))
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-5">
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
            <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Total Credit</p>
            <p class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">KES {{ number_format($stats['total_credit'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
            <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Remaining</p>
            <p class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">KES {{ number_format($stats['remaining_credit'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
            <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Applied</p>
            <p class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">KES {{ number_format($stats['applied_credit'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
            <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Issued</p>
            <p class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ $stats['issued'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
            <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Total</p>
            <p class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ $stats['total'] ?? 0 }}</p>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
        <form method="GET" action="{{ route('user.credit-notes.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
                <label for="search" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Search</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Credit note #, invoice #, client..."
                    class="block w-full rounded-xl border-gray-200 dark:border-[#333333] dark:bg-black/50 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5" />
            </div>

            <div>
                <label for="status" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Status</label>
                <select
                    id="status"
                    name="status"
                    class="block w-full rounded-xl border-gray-200 dark:border-[#333333] dark:bg-black/50 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Issued</option>
                    <option value="applied" {{ request('status') == 'applied' ? 'selected' : '' }}>Applied</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div>
                <label for="dateRange" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Date Range</label>
                <select
                    id="dateRange"
                    name="dateRange"
                    class="block w-full rounded-xl border-gray-200 dark:border-[#333333] dark:bg-black/50 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5">
                    <option value="">All Time</option>
                    <option value="today" {{ request('dateRange') == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ request('dateRange') == 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ request('dateRange') == 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="quarter" {{ request('dateRange') == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                    <option value="year" {{ request('dateRange') == 'year' ? 'selected' : '' }}>This Year</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-transparent shadow-sm text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 uppercase tracking-wider">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Credit Notes Table -->
    @if(isset($creditNotes) && $creditNotes->count() > 0)
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Credit Note #</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Invoice</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Client</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Issue Date</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Total Credit</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Remaining</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#2A2A2A]">
                    @foreach($creditNotes as $creditNote)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('user.credit-notes.show', $creditNote['id']) }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-900 transition-colors duration-150">
                                {{ $creditNote['credit_note_number'] ?? 'CN-' . str_pad($creditNote['id'], 3, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            <a href="{{ route('user.invoices.show', $creditNote['invoice']['id']) }}" class="text-indigo-600 hover:text-indigo-900">
                                {{ $creditNote['invoice']['invoice_number'] }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            {{ $creditNote['client']['name'] ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                            $statusConfig = match(strtolower($creditNote['status'] ?? 'draft')) {
                            'issued' => ['bg' => 'bg-blue-500/10', 'text' => 'text-blue-600 dark:text-blue-400', 'border' => 'border-blue-500/20'],
                            'applied' => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-600 dark:text-emerald-400', 'border' => 'border-emerald-500/20'],
                            'cancelled' => ['bg' => 'bg-red-500/10', 'text' => 'text-red-600 dark:text-red-400', 'border' => 'border-red-500/20'],
                            default => ['bg' => 'bg-gray-500/10', 'text' => 'text-gray-600 dark:text-gray-400', 'border' => 'border-gray-500/20']
                            };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest border {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} {{ $statusConfig['border'] }}">
                                {{ ucfirst($creditNote['status'] ?? 'draft') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            {{ \Carbon\Carbon::parse($creditNote['issue_date'])->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900 dark:text-white">
                            KES {{ number_format($creditNote['total_credit'], 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900 dark:text-white">
                            KES {{ number_format($creditNote['remaining_credit'], 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                <a href="{{ route('user.credit-notes.show', $creditNote['id']) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs uppercase tracking-wider" title="View">View</a>
                                @if(($creditNote['status'] ?? 'draft') === 'draft')
                                <a href="{{ route('user.credit-notes.edit', $creditNote['id']) }}" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 font-bold text-xs uppercase tracking-wider" title="Edit">Edit</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(method_exists($creditNotes, 'links'))
        <div class="px-6 py-4 border-t border-gray-100 dark:border-[#2A2A2A]">
            {{ $creditNotes->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-12 text-center shadow-sm">
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-[#9A9A9A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No credit notes</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-[#9A9A9A]">Get started by creating a credit note from an invoice.</p>
        <div class="mt-6">
            <a href="{{ route('user.credit-notes.create') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 uppercase tracking-wider">
                New Credit Note
            </a>
        </div>
    </div>
    @endif
</div>
@endsection