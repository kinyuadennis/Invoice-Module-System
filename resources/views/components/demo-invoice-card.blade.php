@props(['invoice'])

@php
    $borderColor = match($invoice['status']) {
        'paid' => 'border-l-emerald-500',
        'overdue' => 'border-l-rose-500',
        'sent' => 'border-l-indigo-500',
        default => 'border-l-gray-400',
    };
    
    $statusColor = match($invoice['status']) {
        'paid' => 'bg-emerald-100 text-emerald-700',
        'overdue' => 'bg-rose-100 text-rose-700',
        'sent' => 'bg-indigo-100 text-indigo-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<div class="bg-white rounded-xl shadow-md border-l-4 {{ $borderColor }} p-6 hover:shadow-xl hover:scale-105 transition-all duration-300">
    <div class="flex items-start justify-between mb-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900">{{ $invoice['invoice_number'] }}</h3>
            <p class="text-sm text-gray-600 mt-1">{{ $invoice['client_name'] }}</p>
        </div>
        <span class="px-3 py-1 {{ $statusColor }} rounded-full text-xs font-semibold uppercase">
            {{ $invoice['status'] }}
        </span>
    </div>

    <div class="mb-4">
        <p class="text-2xl font-black text-gray-900">KSh {{ number_format($invoice['total'], 0) }}</p>
        @if($invoice['due_date'])
            <p class="text-xs text-gray-500 mt-1">Due: {{ \Carbon\Carbon::parse($invoice['due_date'])->format('M d') }}</p>
        @endif
    </div>

    <a href="{{ route('register') }}" class="block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-semibold">
        Create Yours →
    </a>
</div>

