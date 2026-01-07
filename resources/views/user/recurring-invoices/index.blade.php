@extends('layouts.user')

@section('title', 'Recurring Invoices')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Recurring Invoices</h1>
            <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Manage automated recurring invoice templates</p>
        </div>
        <a href="{{ route('user.recurring-invoices.create') }}">
            <x-button variant="primary" icon="plus">Create Recurring Invoice</x-button>
        </a>
    </div>

    <!-- Filters -->
    @if(isset($filters) && !empty(array_filter($filters)))
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
        <form method="GET" action="{{ route('user.recurring-invoices.index') }}" class="flex gap-4">
            <x-select name="status" class="w-48">
                <option value="">All Statuses</option>
                <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="paused" {{ ($filters['status'] ?? '') === 'paused' ? 'selected' : '' }}>Paused</option>
                <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
            </x-select>
            <x-button type="submit" variant="primary">Filter</x-button>
            <a href="{{ route('user.recurring-invoices.index') }}">
                <x-button variant="secondary">Clear</x-button>
            </a>
        </form>
    </div>
    @endif

    <!-- Recurring Invoices List -->
    @if($recurringInvoices->count() > 0)
    <div class="grid gap-4">
        @foreach($recurringInvoices as $recurring)
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm hover:border-blue-500/30 transition-colors duration-200">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">
                            <a href="{{ route('user.recurring-invoices.show', $recurring) }}" class="hover:text-indigo-600 transition-colors">
                                {{ $recurring->name }}
                            </a>
                        </h3>
                        @php
                        $statusVariant = match($recurring->status) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                        default => 'default'
                        };
                        $statusColor = match($recurring->status) {
                        'active' => 'text-emerald-600 bg-emerald-500/10 border-emerald-500/20',
                        'paused' => 'text-amber-600 bg-amber-500/10 border-amber-500/20',
                        'cancelled' => 'text-red-600 bg-red-500/10 border-red-500/20',
                        'completed' => 'text-blue-600 bg-blue-500/10 border-blue-500/20',
                        default => 'text-gray-600 bg-gray-500/10 border-gray-500/20'
                        };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest border {{ $statusColor }}">
                            {{ ucfirst($recurring->status) }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">{{ $recurring->description }}</p>
                    <div class="mt-4 flex flex-wrap gap-6 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-[#9A9A9A]">
                        <span>Client: <span class="text-gray-700 dark:text-gray-300">{{ $recurring->client->name }}</span></span>
                        <span>Frequency: <span class="text-gray-700 dark:text-gray-300">Every {{ $recurring->interval }} {{ str($recurring->frequency)->plural() }}</span></span>
                        <span>Next Run: <span class="text-gray-700 dark:text-gray-300">{{ $recurring->next_run_date->format('M d, Y') }}</span></span>
                        <span>Generated: <span class="text-gray-700 dark:text-gray-300">{{ $recurring->total_generated }} invoice(s)</span></span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($recurring->status === 'active')
                    <form method="POST" action="{{ route('user.recurring-invoices.pause', $recurring) }}">
                        @csrf
                        <button type="submit" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Pause">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                    </form>
                    @elseif($recurring->status === 'paused')
                    <form method="POST" action="{{ route('user.recurring-invoices.resume', $recurring) }}">
                        @csrf
                        <button type="submit" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Resume">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                    </form>
                    @endif
                    @if($recurring->status !== 'cancelled')
                    <form method="POST" action="{{ route('user.recurring-invoices.cancel', $recurring) }}" onsubmit="return confirm('Are you sure you want to cancel this recurring invoice?');">
                        @csrf
                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Cancel">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('user.recurring-invoices.show', $recurring) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="View">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $recurringInvoices->links() }}
    </div>
    @else
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-[#9A9A9A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No recurring invoices</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-[#9A9A9A]">Get started by creating your first recurring invoice template.</p>
        <div class="mt-6">
            <a href="{{ route('user.recurring-invoices.create') }}">
                <x-button variant="primary">Create Recurring Invoice</x-button>
            </a>
        </div>
    </div>
    @endif
</div>
@endsection