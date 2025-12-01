@extends('layouts.admin')

@section('title', 'All Invoices')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">All Invoices</h1>
        <p class="mt-1 text-sm text-gray-600">View and manage invoices from all users</p>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('admin.invoices.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <x-input type="text" name="search" label="Search" value="{{ request('search') }}" placeholder="Invoice #, client, user..." />
            <x-select name="status" label="Status" :options="[
                ['value' => '', 'label' => 'All Statuses'],
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'sent', 'label' => 'Sent'],
                ['value' => 'paid', 'label' => 'Paid'],
                ['value' => 'overdue', 'label' => 'Overdue'],
            ]" value="{{ request('status') }}" />
            <x-select name="company_id" label="Company" :options="array_merge([['value' => '', 'label' => 'All Companies']], $companies->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray())" value="{{ request('company_id') }}" />
            <div class="flex items-end">
                <x-button type="submit" variant="primary" class="w-full">Filter</x-button>
            </div>
        </form>
    </x-card>

    @if(isset($invoices) && $invoices->count() > 0)
        <x-card padding="none">
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </x-slot>
                @foreach($invoices as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('admin.invoices.show', $invoice['id']) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                {{ $invoice['invoice_reference'] ?? $invoice['invoice_number'] ?? 'INV-' . str_pad($invoice['id'], 3, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $invoice['client']['name'] ?? 'Unknown' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @if($invoice['company'])
                                <a href="{{ route('admin.companies.show', $invoice['company']['id']) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $invoice['company']['name'] }}
                                </a>
                            @else
                                <span class="text-gray-400">No Company</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $invoice['user']['name'] ?? 'Unknown' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusVariant = match(strtolower($invoice['status'] ?? 'draft')) {
                                    'paid' => 'success',
                                    'sent' => 'info',
                                    'overdue' => 'danger',
                                    default => 'default'
                                };
                            @endphp
                            <x-badge :variant="$statusVariant">{{ ucfirst($invoice['status'] ?? 'draft') }}</x-badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                            KES {{ number_format($invoice['grand_total'] ?? $invoice['total'] ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('admin.invoices.show', $invoice['id']) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>
    @else
        <x-card>
            <div class="text-center py-12">
                <p class="text-sm text-gray-500">No invoices found</p>
            </div>
        </x-card>
    @endif
</div>
@endsection

