@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Invoices</h1>
            <p class="mt-1 text-sm text-gray-600">Manage and track all your invoices</p>
        </div>
        <a href="{{ route('invoices.create') }}">
            <x-button variant="primary">
                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Invoice
            </x-button>
        </a>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('invoices.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
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
    @if(isset($invoices) && $invoices->count() > 0)
    <div x-data="{ 
            selected: [],
            allSelected: false,
            toggleAll() {
                this.allSelected = !this.allSelected;
                if (this.allSelected) {
                    this.selected = [{{ $invoices->pluck('id')->implode(',') }}];
                } else {
                    this.selected = [];
                }
            },
            async performAction(action, data = {}) {
                if (this.selected.length === 0) return;
                
                if (!confirm('Are you sure you want to perform this action on ' + this.selected.length + ' invoices?')) return;

                try {
                    const response = await fetch(`/app/invoices/bulk-${action}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            ids: this.selected,
                            ...data
                        })
                    });

                    const result = await response.json();
                    
                    if (result.success) {
                        window.location.reload();
                    } else {
                        alert(result.message || 'Action failed');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while processing your request.');
                }
            }
        }">
        <!-- Bulk Actions Toolbar -->
        <div x-show="selected.length > 0"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="fixed bottom-4 left-1/2 transform -translate-x-1/2 z-50 bg-white border border-gray-200 shadow-lg rounded-lg px-6 py-3 flex items-center space-x-4">
            <span class="text-sm font-medium text-gray-700" x-text="selected.length + ' selected'"></span>
            <div class="h-4 w-px bg-gray-300"></div>
            <button @click="performAction('send')" class="text-sm font-medium text-gray-600 hover:text-indigo-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Send Email
            </button>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" class="text-sm font-medium text-gray-600 hover:text-indigo-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Change Status
                </button>
                <div x-show="open" class="absolute bottom-full mb-2 left-0 w-48 bg-white rounded-md shadow-lg border border-gray-100 py-1">
                    <button @click="performAction('status', { status: 'sent' })" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mark as Sent</button>
                    <button @click="performAction('status', { status: 'paid' })" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mark as Paid</button>
                    <button @click="performAction('status', { status: 'cancelled' })" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mark as Cancelled</button>
                </div>
            </div>
            <button @click="performAction('delete')" class="text-sm font-medium text-red-600 hover:text-red-700 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Delete
            </button>
        </div>

        <x-card padding="none">
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox"
                                @click="toggleAll()"
                                :checked="allSelected"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </x-slot>
                @foreach($invoices as $invoice)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox"
                            value="{{ $invoice['id'] }}"
                            x-model="selected"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('invoices.show', $invoice['id']) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                            {{ $invoice['invoice_number'] ?? 'INV-' . str_pad($invoice['id'], 3, '0', STR_PAD_LEFT) }}
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $invoice['client']['name'] ?? 'Unknown' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                        $statusVariant = match(strtolower($invoice['status'] ?? 'draft')) {
                        'paid' => 'success',
                        'sent' => 'info',
                        'overdue' => 'danger',
                        'pending' => 'warning',
                        default => 'default'
                        };
                        @endphp
                        <x-badge :variant="$statusVariant">{{ ucfirst($invoice['status'] ?? 'draft') }}</x-badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $invoice['issue_date'] ? \Carbon\Carbon::parse($invoice['issue_date'])->format('M d, Y') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $invoice['due_date'] ? \Carbon\Carbon::parse($invoice['due_date'])->format('M d, Y') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                        ${{ number_format($invoice['total'] ?? 0, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('invoices.show', $invoice['id']) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                            <a href="{{ route('invoices.edit', $invoice['id']) }}" class="text-gray-600 hover:text-gray-900">Edit</a>
                            @if(($invoice['status'] ?? 'draft') === 'draft')
                            <form method="POST" action="{{ route('invoices.destroy', $invoice['id']) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </x-table>

            <!-- Pagination -->
            @if(method_exists($invoices, 'links'))
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $invoices->links() }}
            </div>
            @endif
        </x-card>
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
                <a href="{{ route('invoices.create') }}">
                    <x-button variant="primary">New Invoice</x-button>
                </a>
            </div>
        </div>
    </x-card>
    @endif
</div>
@endsection