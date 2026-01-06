@extends('layouts.user')

@section('title', 'Credit Notes')

@section('content')
<div class="space-y-6 mb-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Credit Notes</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Manage credit notes and refunds</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('user.credit-notes.create') }}">
                <x-button variant="primary">
                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Credit Note
                </x-button>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    @if(isset($stats))
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Total Credit</p>
                        <p class="text-2xl font-bold text-gray-900">KES {{ number_format($stats['total_credit'] ?? 0, 2) }}</p>
                    </div>
                </div>
            </x-card>
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Remaining</p>
                        <p class="text-2xl font-bold text-gray-900">KES {{ number_format($stats['remaining_credit'] ?? 0, 2) }}</p>
                    </div>
                </div>
            </x-card>
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Applied</p>
                        <p class="text-2xl font-bold text-gray-900">KES {{ number_format($stats['applied_credit'] ?? 0, 2) }}</p>
                    </div>
                </div>
            </x-card>
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Issued</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['issued'] ?? 0 }}</p>
                    </div>
                </div>
            </x-card>
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Total</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                </div>
            </x-card>
        </div>
    @endif

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('user.credit-notes.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
                <x-input 
                    type="text" 
                    name="search" 
                    label="Search" 
                    value="{{ request('search') }}"
                    placeholder="Credit note #, invoice #, client..."
                />
            </div>
            
            <div>
                <x-select 
                    name="status" 
                    label="Status"
                    :options="[
                        ['value' => '', 'label' => 'All Statuses'],
                        ['value' => 'draft', 'label' => 'Draft'],
                        ['value' => 'issued', 'label' => 'Issued'],
                        ['value' => 'applied', 'label' => 'Applied'],
                        ['value' => 'cancelled', 'label' => 'Cancelled'],
                    ]"
                    value="{{ request('status') }}"
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

    <!-- Credit Notes Table -->
    @if(isset($creditNotes) && $creditNotes->count() > 0)
        <x-card padding="none">
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Credit Note #</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Invoice</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Issue Date</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Credit</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Remaining</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </x-slot>
                @foreach($creditNotes as $creditNote)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-5 py-3 whitespace-nowrap">
                            <a href="{{ route('user.credit-notes.show', $creditNote['id']) }}" class="text-sm font-medium text-[#2B6EF6] hover:text-[#2563EB] transition-colors duration-150">
                                {{ $creditNote['credit_note_number'] ?? 'CN-' . str_pad($creditNote['id'], 3, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-900">
                            <a href="{{ route('user.invoices.show', $creditNote['invoice']['id']) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $creditNote['invoice']['invoice_number'] }}
                            </a>
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $creditNote['client']['name'] ?? 'N/A' }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap">
                            @php
                                $statusVariant = match(strtolower($creditNote['status'] ?? 'draft')) {
                                    'issued' => 'info',
                                    'applied' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'default'
                                };
                            @endphp
                            <x-badge :variant="$statusVariant">{{ ucfirst($creditNote['status'] ?? 'draft') }}</x-badge>
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            {{ \Carbon\Carbon::parse($creditNote['issue_date'])->format('M d, Y') }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                            KES {{ number_format($creditNote['total_credit'], 2) }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                            KES {{ number_format($creditNote['remaining_credit'], 2) }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('user.credit-notes.show', $creditNote['id']) }}" class="text-indigo-600 hover:text-indigo-900" title="View">View</a>
                                @if(($creditNote['status'] ?? 'draft') === 'draft')
                                    <a href="{{ route('user.credit-notes.edit', $creditNote['id']) }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900" title="Edit">Edit</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <!-- Pagination -->
            @if(method_exists($creditNotes, 'links'))
                <div class="px-5 py-3 border-t border-gray-200">
                    {{ $creditNotes->links() }}
                </div>
            @endif
        </x-card>
    @else
        <x-card>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No credit notes</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a credit note from an invoice.</p>
                <div class="mt-6">
                    <a href="{{ route('user.credit-notes.create') }}">
                        <x-button variant="primary">New Credit Note</x-button>
                    </a>
                </div>
            </div>
        </x-card>
    @endif
</div>
@endsection

