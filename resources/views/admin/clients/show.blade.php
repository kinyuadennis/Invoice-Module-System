@extends('layouts.admin')

@section('title', 'Client Details')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $client['name'] ?? 'Client' }}</h1>
            <p class="mt-1 text-sm text-gray-600">Client information and invoices</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.clients.edit', $client['id']) }}">
                <x-button variant="primary">Edit Client</x-button>
            </a>
            <a href="{{ route('admin.clients.index') }}">
                <x-button variant="outline">Back to Clients</x-button>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Client Information -->
        <div class="lg:col-span-2">
            <x-card>
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Client Information</h2>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $client['name'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $client['email'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $client['phone'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">KRA PIN</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $client['kra_pin'] ?? 'N/A' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $client['address'] ?? 'N/A' }}</dd>
                    </div>
                    @if(isset($client['company']) && $client['company'])
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Company</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="{{ route('admin.companies.show', $client['company']['id']) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $client['company']['name'] }}
                                </a>
                            </dd>
                        </div>
                    @endif
                </dl>
            </x-card>
        </div>
    </div>

    <!-- Invoices -->
    @if(isset($client['invoices']) && count($client['invoices']) > 0)
        <x-card padding="none">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Invoices</h2>
            </div>
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </x-slot>
                @foreach($client['invoices'] as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $invoice->invoice_reference ?? 'INV-' . str_pad($invoice->id, 3, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusVariant = match(strtolower($invoice->status ?? 'draft')) {
                                    'paid' => 'success',
                                    'sent' => 'info',
                                    'overdue' => 'danger',
                                    default => 'default'
                                };
                            @endphp
                            <x-badge :variant="$statusVariant">{{ ucfirst($invoice->status ?? 'draft') }}</x-badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                            KES {{ number_format($invoice->grand_total ?? $invoice->total ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>
    @endif
</div>
@endsection

