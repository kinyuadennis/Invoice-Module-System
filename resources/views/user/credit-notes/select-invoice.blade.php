@extends('layouts.user')

@section('title', 'Create Credit Note - Select Invoice')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Create Credit Note</h1>
        <p class="mt-1 text-sm text-gray-600">Select the invoice to create a credit note for</p>
    </div>

    <x-card>
        <div class="space-y-4">
            @if($invoices->count() > 0)
                <div class="space-y-2">
                    @foreach($invoices as $invoice)
                        <a href="{{ route('user.credit-notes.create', ['invoice_id' => $invoice->id]) }}" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $invoice->full_number ?? $invoice->invoice_reference }}</p>
                                    <p class="text-sm text-gray-600">{{ $invoice->client->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">KES {{ number_format($invoice->grand_total ?? 0, 2) }} • {{ ucfirst($invoice->status) }}</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-500">No invoices available to create credit notes from.</p>
                </div>
            @endif
        </div>
    </x-card>
</div>
@endsection

