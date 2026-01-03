@extends('layouts.admin')

@section('title', $company->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            @if($company->logo)
                <img src="{{ Storage::url($company->logo) }}" alt="{{ $company->name }}" class="h-16 w-16 rounded-lg object-cover">
            @else
                <div class="h-16 w-16 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <span class="text-2xl text-indigo-600 font-semibold">{{ strtoupper(substr($company->name, 0, 1)) }}</span>
                </div>
            @endif
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $company->name }}</h1>
                <p class="mt-1 text-sm text-gray-600">Company details and statistics</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.companies.edit', $company->id) }}">
                <x-button variant="primary">Edit Company</x-button>
            </a>
            <a href="{{ route('admin.companies.index') }}">
                <x-button variant="outline">Back to Companies</x-button>
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
        <x-card padding="sm">
            <div class="text-center">
                <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">KES {{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
            </div>
        </x-card>
        <x-card padding="sm">
            <div class="text-center">
                <p class="text-sm font-medium text-gray-500">Pending Revenue</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">KES {{ number_format($stats['pending_revenue'] ?? 0, 2) }}</p>
            </div>
        </x-card>
        <x-card padding="sm">
            <div class="text-center">
                <p class="text-sm font-medium text-gray-500">Overdue</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">KES {{ number_format($stats['overdue_revenue'] ?? 0, 2) }}</p>
            </div>
        </x-card>
        <x-card padding="sm">
            <div class="text-center">
                <p class="text-sm font-medium text-gray-500">Total Payments</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">KES {{ number_format($stats['total_payments'] ?? 0, 2) }}</p>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Company Information -->
        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Company Information</h2>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Company Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->email ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->phone ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">KRA PIN</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->kra_pin ?? 'N/A' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->address ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Invoice Prefix</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->invoice_prefix ?? 'INV' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $company->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </x-card>

            <!-- Owner Information -->
            @if($company->owner)
                <x-card>
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Owner</h2>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $company->owner->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $company->owner->email }}</dd>
                        </div>
                    </dl>
                </x-card>
            @endif
        </div>

        <!-- Quick Stats -->
        <div class="space-y-6">
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Stats</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Users</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $company->users_count ?? 0 }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Clients</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $company->clients_count ?? 0 }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Invoices</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $company->invoices_count ?? 0 }}</dd>
                    </div>
                </dl>
            </x-card>
        </div>
    </div>

    <!-- Recent Invoices -->
    @if($recentInvoices->count() > 0)
        <x-card padding="none">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Recent Invoices</h2>
            </div>
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </x-slot>
                @foreach($recentInvoices as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $invoice->invoice_reference ?? 'INV-' . str_pad($invoice->id, 3, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $invoice->client->name ?? 'Unknown' }}
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

