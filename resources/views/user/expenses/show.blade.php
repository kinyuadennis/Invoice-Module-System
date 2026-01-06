@extends('layouts.user')

@section('title', 'Expense Details')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Expense {{ $expense['expense_number'] ?? 'EXP-' . str_pad($expense['id'], 3, '0', STR_PAD_LEFT) }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">View expense details</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('user.expenses.edit', $expense['id']) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
        </div>
    </div>

    <!-- Expense Details Card -->
    <x-card>
        <div class="space-y-6">
            <!-- Status Badge -->
            <div class="flex items-center justify-between">
                <div>
                    @php
                        $statusVariant = match(strtolower($expense['status'] ?? 'pending')) {
                            'approved', 'paid' => 'success',
                            'rejected' => 'danger',
                            default => 'warning'
                        };
                    @endphp
                    <x-badge :variant="$statusVariant" class="text-lg px-4 py-2">{{ ucfirst($expense['status'] ?? 'pending') }}</x-badge>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-bold text-gray-900">KES {{ number_format($expense['amount'], 2) }}</p>
                    <p class="text-sm text-gray-500">Total Amount</p>
                </div>
            </div>

            <!-- Expense Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-200 pt-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Expense Information</h3>
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="text-gray-500">Description:</span>
                            <span class="font-medium text-gray-900 ml-2">{{ $expense['description'] }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Date:</span>
                            <span class="font-medium text-gray-900 ml-2">{{ \Carbon\Carbon::parse($expense['expense_date'])->format('M d, Y') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Category:</span>
                            @if(isset($expense['category']))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ml-2" style="background-color: {{ $expense['category']['color'] }}20; color: {{ $expense['category']['color'] }}">
                                    {{ $expense['category']['name'] }}
                                </span>
                            @else
                                <span class="text-gray-500 ml-2">Uncategorized</span>
                            @endif
                        </div>
                        @if(isset($expense['vendor_name']))
                            <div>
                                <span class="text-gray-500">Vendor:</span>
                                <span class="font-medium text-gray-900 ml-2">{{ $expense['vendor_name'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Payment Details</h3>
                    <div class="space-y-2 text-sm">
                        @if(isset($expense['payment_method']))
                            <div>
                                <span class="text-gray-500">Payment Method:</span>
                                <span class="font-medium text-gray-900 ml-2">{{ ucfirst(str_replace('_', ' ', $expense['payment_method'])) }}</span>
                            </div>
                        @endif
                        @if(isset($expense['reference_number']))
                            <div>
                                <span class="text-gray-500">Reference:</span>
                                <span class="font-medium text-gray-900 ml-2">{{ $expense['reference_number'] }}</span>
                            </div>
                        @endif
                        @if(isset($expense['client']))
                            <div>
                                <span class="text-gray-500">Linked Client:</span>
                                <span class="font-medium text-gray-900 ml-2">{{ $expense['client']['name'] }}</span>
                            </div>
                        @endif
                        @if(isset($expense['invoice']))
                            <div>
                                <span class="text-gray-500">Linked Invoice:</span>
                                <a href="{{ route('user.invoices.show', $expense['invoice']['id']) }}" class="font-medium text-blue-600 hover:text-blue-800 ml-2">
                                    {{ $expense['invoice']['invoice_number'] }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tax Information -->
            @if(($expense['tax_deductible'] ?? false) && ($expense['tax_amount'] ?? 0) > 0)
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Tax Information</h3>
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-300">Tax Deductible Amount</p>
                                <p class="text-lg font-semibold text-gray-900">KES {{ number_format($expense['amount'], 2) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600 dark:text-gray-300">Estimated VAT (16%)</p>
                                <p class="text-lg font-semibold text-gray-900">KES {{ number_format($expense['tax_amount'], 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Receipt -->
            @if(isset($expense['receipt_path']) && $expense['receipt_path'])
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Receipt</h3>
                    <div class="mt-2">
                        <a href="{{ Storage::url($expense['receipt_path']) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            View Receipt
                        </a>
                    </div>
                </div>
            @endif

            <!-- Notes -->
            @if(isset($expense['notes']) && $expense['notes'])
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Notes</h3>
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $expense['notes'] }}</p>
                </div>
            @endif

            <!-- Recorded By -->
            @if(isset($expense['user']))
                <div class="border-t border-gray-200 pt-6">
                    <p class="text-xs text-gray-500">
                        Recorded by {{ $expense['user']['name'] }} on {{ \Carbon\Carbon::parse($expense['expense_date'])->format('M d, Y') }}
                    </p>
                </div>
            @endif
        </div>
    </x-card>
</div>
@endsection

