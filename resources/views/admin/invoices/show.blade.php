@extends('layouts.admin')

@section('title', 'Invoice Details')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Invoice {{ $invoice['invoice_number'] ?? 'N/A' }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">View invoice details</p>
    </div>

    <x-card>
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <p class="text-sm font-medium text-gray-500">Invoice Number</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">{{ $invoice['invoice_reference'] ?? $invoice['invoice_number'] ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Status</p>
                <p class="mt-1">
                    @php
                        $statusVariant = match(strtolower($invoice['status'] ?? 'draft')) {
                            'paid' => 'success',
                            'sent' => 'info',
                            'overdue' => 'danger',
                            default => 'default'
                        };
                    @endphp
                    <x-badge :variant="$statusVariant">{{ ucfirst($invoice['status'] ?? 'draft') }}</x-badge>
                </p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Client</p>
                <p class="mt-1 text-sm text-gray-900">{{ $invoice['client']['name'] ?? 'Unknown' }}</p>
            </div>
            @if(isset($invoice['company']) && $invoice['company'])
                <div>
                    <p class="text-sm font-medium text-gray-500">Company</p>
                    <p class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('admin.companies.show', $invoice['company']['id']) }}" class="text-indigo-600 hover:text-indigo-900">
                            {{ $invoice['company']['name'] }}
                        </a>
                    </p>
                </div>
            @endif
            <div>
                <p class="text-sm font-medium text-gray-500">Created By</p>
                <p class="mt-1 text-sm text-gray-900">{{ $invoice['user']['name'] ?? 'Unknown' }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">KES {{ number_format($invoice['grand_total'] ?? $invoice['total'] ?? 0, 2) }}</p>
            </div>
        </div>
    </x-card>
</div>
@endsection

