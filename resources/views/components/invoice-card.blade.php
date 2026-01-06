@props(['invoice'])

@php
    // Status-based border color
    $borderColor = match($invoice['status']) {
        'paid' => 'border-l-emerald-500',
        'overdue' => 'border-l-rose-500',
        default => 'border-l-indigo-500',
    };

    // Status badge variant
    $statusVariant = match($invoice['status']) {
        'paid' => 'success',
        'overdue' => 'danger',
        'sent' => 'info',
        'draft' => 'default',
        default => 'default',
    };
@endphp

<div class="bg-white rounded-lg shadow-sm border-l-4 {{ $borderColor }} border border-gray-200 p-6 hover:shadow-md transition-shadow">
    <div class="flex items-start justify-between mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ $invoice['invoice_number'] }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $invoice['client_name'] }}</p>
        </div>
        <x-badge :variant="$statusVariant">{{ ucfirst($invoice['status']) }}</x-badge>
    </div>

    <div class="space-y-2 mb-4">
        <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600 dark:text-gray-300">Total Amount</span>
            <span class="text-lg font-bold text-gray-900">KSh {{ number_format($invoice['total'], 2) }}</span>
        </div>
        @if($invoice['platform_fee'] > 0)
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500">Platform Fee</span>
                <span class="text-gray-700 dark:text-gray-200">KSh {{ number_format($invoice['platform_fee'], 2) }}</span>
            </div>
        @endif
        @if($invoice['due_date'])
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500">Due Date</span>
                <span class="text-gray-700 dark:text-gray-200">{{ \Carbon\Carbon::parse($invoice['due_date'])->format('M d, Y') }}</span>
            </div>
        @endif
    </div>
</div>

