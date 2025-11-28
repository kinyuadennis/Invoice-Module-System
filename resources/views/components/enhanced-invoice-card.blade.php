@props(['invoice', 'showActions' => false])

@php
    $borderColor = match($invoice['status']) {
        'paid' => 'border-l-emerald-500',
        'overdue' => 'border-l-rose-500',
        'sent' => 'border-l-indigo-500',
        'draft' => 'border-l-gray-400',
        default => 'border-l-gray-400',
    };
    
    $statusColor = match($invoice['status']) {
        'paid' => 'bg-emerald-100 text-emerald-700',
        'overdue' => 'bg-rose-100 text-rose-700',
        'sent' => 'bg-indigo-100 text-indigo-700',
        'draft' => 'bg-gray-100 text-gray-700',
        default => 'bg-gray-100 text-gray-700',
    };

    // Determine payment method (mock for now - would come from database)
    $paymentMethod = $invoice['status'] === 'paid' ? 'mpesa' : null;
@endphp

<div 
    class="bg-white rounded-xl shadow-md border-l-4 {{ $borderColor }} p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 group"
    x-data="{ showActions: false }"
    @mouseenter="showActions = true"
    @mouseleave="showActions = false"
>
    <!-- Header -->
    <div class="flex items-start justify-between mb-4">
        <div class="flex-1">
            <h3 class="text-lg font-bold text-gray-900">{{ $invoice['invoice_number'] }}</h3>
            <p class="text-sm text-gray-600 mt-1">{{ $invoice['client_name'] }}</p>
        </div>
        <div class="flex flex-col items-end gap-2">
            <span class="px-3 py-1 {{ $statusColor }} rounded-full text-xs font-semibold uppercase">
                {{ $invoice['status'] }}
            </span>
            @if($paymentMethod === 'mpesa' && $invoice['status'] === 'paid')
                <div class="flex items-center px-2 py-1 bg-green-50 rounded-full">
                    <svg class="w-3 h-3 text-green-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                        <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                    </svg>
                    <span class="text-xs font-semibold text-green-700">M-Pesa</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Amount -->
    <div class="mb-4">
        <p class="text-2xl font-black text-gray-900">KES {{ number_format($invoice['total'], 0) }}</p>
        @if(isset($invoice['due_date']) && $invoice['due_date'])
            <p class="text-xs text-gray-500 mt-1">
                Due: {{ \Carbon\Carbon::parse($invoice['due_date'])->format('M d, Y') }}
            </p>
        @endif
    </div>

    <!-- Quick Actions (on hover) -->
    @if($showActions)
    <div 
        x-show="showActions"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        class="flex gap-2 mb-4"
    >
        <button class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-xs font-semibold transition-colors">
            Send Reminder
        </button>
        <button class="flex-1 px-3 py-2 bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-200 text-xs font-semibold transition-colors">
            Mark Paid
        </button>
    </div>
    @endif

    <!-- CTA -->
    <a href="{{ route('register') }}" class="block w-full text-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors text-sm font-semibold">
        Create Yours →
    </a>
</div>

