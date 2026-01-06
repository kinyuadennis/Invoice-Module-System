@extends('layouts.user')

@section('title', 'Recurring Invoices')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Recurring Invoices</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Manage automated recurring invoice templates</p>
        </div>
        <a href="{{ route('user.recurring-invoices.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
            Create Recurring Invoice
        </a>
    </div>

    <!-- Filters -->
    @if(isset($filters) && !empty(array_filter($filters)))
    <x-card>
        <form method="GET" action="{{ route('user.recurring-invoices.index') }}" class="flex gap-4">
            <select name="status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All Statuses</option>
                <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="paused" {{ ($filters['status'] ?? '') === 'paused' ? 'selected' : '' }}>Paused</option>
                <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Filter</button>
            <a href="{{ route('user.recurring-invoices.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300">Clear</a>
        </form>
    </x-card>
    @endif

    <!-- Recurring Invoices List -->
    @if($recurringInvoices->count() > 0)
        <div class="grid gap-4">
            @foreach($recurringInvoices as $recurring)
                <x-card>
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <a href="{{ route('user.recurring-invoices.show', $recurring) }}" class="hover:text-indigo-600">
                                        {{ $recurring->name }}
                                    </a>
                                </h3>
                                <x-badge :variant="match($recurring->status) {
                                    'active' => 'success',
                                    'paused' => 'warning',
                                    'cancelled' => 'danger',
                                    'completed' => 'info',
                                    default => 'default'
                                }">{{ ucfirst($recurring->status) }}</x-badge>
                            </div>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $recurring->description }}</p>
                            <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-500">
                                <span>Client: {{ $recurring->client->name }}</span>
                                <span>Frequency: Every {{ $recurring->interval }} {{ str($recurring->frequency)->plural() }}</span>
                                <span>Next Run: {{ $recurring->next_run_date->format('M d, Y') }}</span>
                                <span>Generated: {{ $recurring->total_generated }} invoice(s)</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            @if($recurring->status === 'active')
                                <form method="POST" action="{{ route('user.recurring-invoices.pause', $recurring) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 text-sm bg-yellow-100 text-yellow-800 rounded-md hover:bg-yellow-200">Pause</button>
                                </form>
                            @elseif($recurring->status === 'paused')
                                <form method="POST" action="{{ route('user.recurring-invoices.resume', $recurring) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 text-sm bg-green-100 text-green-800 rounded-md hover:bg-green-200">Resume</button>
                                </form>
                            @endif
                            @if($recurring->status !== 'cancelled')
                                <form method="POST" action="{{ route('user.recurring-invoices.cancel', $recurring) }}" onsubmit="return confirm('Are you sure you want to cancel this recurring invoice?');">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 text-sm bg-red-100 text-red-800 rounded-md hover:bg-red-200">Cancel</button>
                                </form>
                            @endif
                            <a href="{{ route('user.recurring-invoices.show', $recurring) }}" class="px-3 py-1 text-sm bg-indigo-100 text-indigo-800 rounded-md hover:bg-indigo-200">View</a>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $recurringInvoices->links() }}
        </div>
    @else
        <x-card>
            <div class="text-center py-12">
                <p class="text-sm text-gray-500 mb-4">No recurring invoices found</p>
                <a href="{{ route('user.recurring-invoices.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Create Your First Recurring Invoice
                </a>
            </div>
        </x-card>
    @endif
</div>
@endsection

