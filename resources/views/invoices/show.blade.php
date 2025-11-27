@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Invoice {{ $invoice['invoice_number'] ?? 'INV-' . str_pad($invoice['id'], 3, '0', STR_PAD_LEFT) }}</h1>
            <p class="mt-1 text-sm text-gray-600">View and manage invoice details</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(($invoice['status'] ?? 'draft') === 'draft')
                <a href="{{ route('invoices.edit', $invoice['id']) }}">
                    <x-button variant="outline">Edit</x-button>
                </a>
            @endif
            @if(($invoice['status'] ?? 'draft') !== 'paid')
                <form method="POST" action="{{ route('invoices.update', $invoice['id']) }}" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="paid">
                    <x-button type="submit" variant="primary">Mark as Paid</x-button>
                </form>
            @endif
        </div>
    </div>

    <!-- Invoice Details -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main Invoice Card -->
        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Invoice Details</h2>
                        <p class="text-sm text-gray-600">Invoice #{{ $invoice['invoice_number'] ?? 'INV-' . str_pad($invoice['id'], 3, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    @php
                        $statusVariant = match(strtolower($invoice['status'] ?? 'draft')) {
                            'paid' => 'success',
                            'sent' => 'info',
                            'overdue' => 'danger',
                            'pending' => 'warning',
                            default => 'default'
                        };
                    @endphp
                    <x-badge :variant="$statusVariant">{{ ucfirst($invoice['status'] ?? 'draft') }}</x-badge>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Issue Date</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $invoice['issue_date'] ? \Carbon\Carbon::parse($invoice['issue_date'])->format('M d, Y') : 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Due Date</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $invoice['due_date'] ? \Carbon\Carbon::parse($invoice['due_date'])->format('M d, Y') : 'N/A' }}</p>
                    </div>
                </div>

                <!-- Client Info -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-4">Bill To</h3>
                    <div class="text-sm text-gray-600">
                        <p class="font-medium text-gray-900">{{ $invoice['client']['name'] ?? 'Unknown Client' }}</p>
                        @if(isset($invoice['client']['email']))
                            <p>{{ $invoice['client']['email'] }}</p>
                        @endif
                        @if(isset($invoice['client']['address']))
                            <p class="mt-1">{{ $invoice['client']['address'] }}</p>
                        @endif
                    </div>
                </div>
            </x-card>

            <!-- Line Items -->
            <x-card padding="none">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Line Items</h2>
                </div>
                <x-table>
                    <x-slot name="header">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </x-slot>
                    @foreach($invoice['items'] ?? [] as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['description'] ?? '' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">{{ $item['quantity'] ?? 0 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">${{ number_format($item['rate'] ?? 0, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                ${{ number_format(($item['quantity'] ?? 0) * ($item['rate'] ?? 0), 2) }}
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Summary -->
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Summary</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span class="font-medium text-gray-900">${{ number_format($invoice['subtotal'] ?? 0, 2) }}</span>
                    </div>
                    @if(($invoice['tax_rate'] ?? 0) > 0)
                        <div class="flex justify-between text-gray-600">
                            <span>Tax ({{ $invoice['tax_rate'] ?? 0 }}%)</span>
                            <span class="font-medium text-gray-900">${{ number_format($invoice['tax'] ?? 0, 2) }}</span>
                        </div>
                    @endif
                    @if(($invoice['platform_fee'] ?? 0) > 0)
                        <div class="flex justify-between text-gray-600">
                            <span>Platform Fee</span>
                            <span class="font-medium text-gray-900">${{ number_format($invoice['platform_fee'] ?? 0, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between pt-2 border-t border-gray-200 text-base font-semibold text-gray-900">
                        <span>Total</span>
                        <span class="text-indigo-600">${{ number_format($invoice['total'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </x-card>

            <!-- Notes -->
            @if(isset($invoice['notes']) && $invoice['notes'])
                <x-card>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Notes</h2>
                    <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $invoice['notes'] }}</p>
                </x-card>
            @endif

            <!-- Payments -->
            @if(isset($invoice['payments']) && count($invoice['payments']) > 0)
                <x-card>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Payments</h2>
                    <div class="space-y-3">
                        @foreach($invoice['payments'] as $payment)
                            <div class="flex items-center justify-between text-sm">
                                <div>
                                    <p class="font-medium text-gray-900">${{ number_format($payment['amount'] ?? 0, 2) }}</p>
                                    <p class="text-gray-600">{{ $payment['payment_date'] ? \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y') : 'N/A' }}</p>
                                </div>
                                <x-badge variant="success">Paid</x-badge>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>
    </div>
</div>
@endsection

