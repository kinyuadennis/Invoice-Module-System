@extends('layouts.user')

@section('title', 'Estimate Details')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Estimate {{ $estimate['estimate_number'] ?? 'EST-' . str_pad($estimate['id'], 3, '0', STR_PAD_LEFT) }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">View and manage estimate details</p>
        </div>
        <div class="flex items-center space-x-3 flex-wrap gap-2">
            @if(!($estimate['is_converted'] ?? false))
                @if(($estimate['status'] ?? 'draft') === 'draft')
                    <a href="{{ route('user.estimates.edit', $estimate['id']) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </a>
                    @if(isset($estimate['client']) && isset($estimate['client']['email']))
                        <form method="POST" action="{{ route('user.estimates.send', $estimate['id']) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Send Estimate
                            </button>
                        </form>
                    @endif
                @elseif(($estimate['status'] ?? 'draft') === 'sent')
                    <form method="POST" action="{{ route('user.estimates.send', $estimate['id']) }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Resend
                        </button>
                    </form>
                @endif

                @if(($estimate['status'] ?? 'draft') === 'accepted' || ($estimate['status'] ?? 'draft') === 'sent')
                    <form method="POST" action="{{ route('user.estimates.convert', $estimate['id']) }}" class="inline" onsubmit="return confirm('Convert this estimate to an invoice?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Convert to Invoice
                        </button>
                    </form>
                @endif
            @else
                <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Converted to Invoice
                    @if(isset($estimate['converted_invoice_id']))
                        <a href="{{ route('user.invoices.show', $estimate['converted_invoice_id']) }}" class="ml-2 text-green-900 underline">View Invoice</a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Estimate Details Card -->
    <x-card>
        <div class="space-y-6">
            <!-- Status Badge -->
            <div class="flex items-center justify-between">
                <div>
                    @php
                        $statusVariant = match(strtolower($estimate['status'] ?? 'draft')) {
                            'accepted' => 'success',
                            'sent' => 'info',
                            'rejected' => 'danger',
                            'expired' => 'warning',
                            'converted' => 'success',
                            default => 'default'
                        };
                    @endphp
                    <x-badge :variant="$statusVariant" class="text-lg px-4 py-2">{{ ucfirst($estimate['status'] ?? 'draft') }}</x-badge>
                </div>
                @if($estimate['is_expired'] ?? false)
                    <div class="text-sm text-red-600 font-medium">⚠️ Expired</div>
                @endif
            </div>

            <!-- Client & Company Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Bill To</h3>
                    <div class="text-gray-900">
                        <p class="font-semibold">{{ $estimate['client']['name'] ?? 'N/A' }}</p>
                        @if(isset($estimate['client']['email']))
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $estimate['client']['email'] }}</p>
                        @endif
                        @if(isset($estimate['client']['phone']))
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $estimate['client']['phone'] }}</p>
                        @endif
                        @if(isset($estimate['client']['address']))
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $estimate['client']['address'] }}</p>
                        @endif
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">From</h3>
                    <div class="text-gray-900">
                        <p class="font-semibold">{{ $estimate['company']['name'] ?? 'N/A' }}</p>
                        @if(isset($estimate['company']['email']))
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $estimate['company']['email'] }}</p>
                        @endif
                        @if(isset($estimate['company']['phone']))
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $estimate['company']['phone'] }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Estimate Details -->
            <div class="border-t border-gray-200 pt-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Estimate Number</p>
                        <p class="font-semibold text-gray-900">{{ $estimate['estimate_number'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Issue Date</p>
                        <p class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($estimate['issue_date'])->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Expiry Date</p>
                        <p class="font-semibold text-gray-900">{{ $estimate['expiry_date'] ? \Carbon\Carbon::parse($estimate['expiry_date'])->format('M d, Y') : 'N/A' }}</p>
                    </div>
                    @if(isset($estimate['po_number']))
                        <div>
                            <p class="text-gray-500">PO Number</p>
                            <p class="font-semibold text-gray-900">{{ $estimate['po_number'] }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Items Table -->
            @if(isset($estimate['items']) && count($estimate['items']) > 0)
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($estimate['items'] as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['description'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-300">{{ number_format($item['quantity'], 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-300">KES {{ number_format($item['unit_price'], 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">KES {{ number_format($item['total_price'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Totals -->
            <div class="border-t border-gray-200 pt-6">
                <div class="flex justify-end">
                    <div class="w-full md:w-1/3 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-300">Subtotal:</span>
                            <span class="font-medium text-gray-900">KES {{ number_format($estimate['subtotal'] ?? 0, 2) }}</span>
                        </div>
                        @if(($estimate['vat_amount'] ?? 0) > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-300">VAT (16%):</span>
                                <span class="font-medium text-gray-900">KES {{ number_format($estimate['vat_amount'], 2) }}</span>
                            </div>
                        @endif
                        @if(($estimate['platform_fee'] ?? 0) > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-300">Platform Fee:</span>
                                <span class="font-medium text-gray-900">KES {{ number_format($estimate['platform_fee'], 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2">
                            <span class="text-gray-900">Grand Total:</span>
                            <span class="text-gray-900">KES {{ number_format($estimate['grand_total'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes & Terms -->
            @if(isset($estimate['notes']) || isset($estimate['terms_and_conditions']))
                <div class="border-t border-gray-200 pt-6 space-y-4">
                    @if(isset($estimate['notes']))
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Notes</h3>
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $estimate['notes'] }}</p>
                        </div>
                    @endif
                    @if(isset($estimate['terms_and_conditions']))
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Terms & Conditions</h3>
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $estimate['terms_and_conditions'] }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </x-card>
</div>
@endsection

