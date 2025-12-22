@extends('layouts.user')

@section('title', 'Credit Note Details')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Credit Note {{ $creditNote['credit_note_number'] ?? 'CN-' . str_pad($creditNote['id'], 3, '0', STR_PAD_LEFT) }}</h1>
            <p class="mt-1 text-sm text-gray-600">View credit note details</p>
        </div>
        <div class="flex items-center space-x-3 flex-wrap gap-2">
            @if(($creditNote['status'] ?? 'draft') === 'draft')
                <a href="{{ route('user.credit-notes.edit', $creditNote['id']) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
                <form method="POST" action="{{ route('user.credit-notes.issue', $creditNote['id']) }}" class="inline" onsubmit="return confirm('Issue this credit note? It will be ready to apply to invoices.');">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Issue Credit Note
                    </button>
                </form>
            @elseif(($creditNote['status'] ?? 'draft') === 'issued')
                @if(($creditNote['can_apply_to_invoice'] ?? false) && isset($availableInvoices) && count($availableInvoices) > 0)
                    <button 
                        onclick="document.getElementById('apply-modal').classList.remove('hidden')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Apply to Invoice
                    </button>
                @endif
                @if(!($creditNote['etims_status'] ?? 'pending') === 'approved')
                    <form method="POST" action="{{ route('user.credit-notes.submit-etims', $creditNote['id']) }}" class="inline" onsubmit="return confirm('Submit this credit note to eTIMS for reversal?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Submit to eTIMS
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    <!-- Credit Note Details Card -->
    <x-card>
        <div class="space-y-6">
            <!-- Status Badge -->
            <div class="flex items-center justify-between">
                <div>
                    @php
                        $statusVariant = match(strtolower($creditNote['status'] ?? 'draft')) {
                            'issued' => 'info',
                            'applied' => 'success',
                            'cancelled' => 'danger',
                            default => 'default'
                        };
                    @endphp
                    <x-badge :variant="$statusVariant" class="text-lg px-4 py-2">{{ ucfirst($creditNote['status'] ?? 'draft') }}</x-badge>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-bold text-gray-900">KES {{ number_format($creditNote['total_credit'], 2) }}</p>
                    <p class="text-sm text-gray-500">Total Credit</p>
                    @if(($creditNote['remaining_credit'] ?? 0) > 0)
                        <p class="text-sm text-blue-600 mt-1">KES {{ number_format($creditNote['remaining_credit'], 2) }} remaining</p>
                    @endif
                </div>
            </div>

            <!-- eTIMS Status -->
            @if(isset($creditNote['etims_status']) && $creditNote['etims_status'] !== 'pending')
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-900">eTIMS Status: {{ ucfirst($creditNote['etims_status']) }}</p>
                            @if(isset($creditNote['etims_control_number']))
                                <p class="text-xs text-blue-700 mt-1">Control Number: {{ $creditNote['etims_control_number'] }}</p>
                            @endif
                        </div>
                        @if(isset($creditNote['etims_qr_code']))
                            <div>
                                <img src="{{ $creditNote['etims_qr_code'] }}" alt="eTIMS QR Code" class="w-24 h-24">
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Credit Note Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-200 pt-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Credit Note Information</h3>
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="text-gray-500">Credit Note Number:</span>
                            <span class="font-medium text-gray-900 ml-2">{{ $creditNote['credit_note_number'] }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Issue Date:</span>
                            <span class="font-medium text-gray-900 ml-2">{{ \Carbon\Carbon::parse($creditNote['issue_date'])->format('M d, Y') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Reason:</span>
                            <span class="font-medium text-gray-900 ml-2">{{ ucfirst($creditNote['reason'] ?? 'other') }}</span>
                        </div>
                        @if(isset($creditNote['reason_details']))
                            <div>
                                <span class="text-gray-500">Reason Details:</span>
                                <p class="text-gray-900 mt-1">{{ $creditNote['reason_details'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Original Invoice</h3>
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="text-gray-500">Invoice Number:</span>
                            <a href="{{ route('user.invoices.show', $creditNote['invoice']['id']) }}" class="font-medium text-blue-600 hover:text-blue-800 ml-2">
                                {{ $creditNote['invoice']['invoice_number'] }}
                            </a>
                        </div>
                        <div>
                            <span class="text-gray-500">Client:</span>
                            <span class="font-medium text-gray-900 ml-2">{{ $creditNote['client']['name'] ?? 'N/A' }}</span>
                        </div>
                        @if(isset($creditNote['applied_to_invoice']))
                            <div>
                                <span class="text-gray-500">Applied to Invoice:</span>
                                <a href="{{ route('user.invoices.show', $creditNote['applied_to_invoice']['id']) }}" class="font-medium text-blue-600 hover:text-blue-800 ml-2">
                                    {{ $creditNote['applied_to_invoice']['invoice_number'] }}
                                </a>
                            </div>
                            <div>
                                <span class="text-gray-500">Applied Amount:</span>
                                <span class="font-medium text-gray-900 ml-2">KES {{ number_format($creditNote['applied_amount'] ?? 0, 2) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            @if(isset($creditNote['items']) && count($creditNote['items']) > 0)
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Credited Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($creditNote['items'] as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['description'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">{{ number_format($item['quantity'], 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">KES {{ number_format($item['unit_price'], 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">KES {{ number_format($item['total_price'], 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            <div>
                                                <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $item['credit_reason'] ?? 'other')) }}</span>
                                                @if(isset($item['credit_reason_details']))
                                                    <p class="text-xs text-gray-500 mt-1">{{ $item['credit_reason_details'] }}</p>
                                                @endif
                                            </div>
                                        </td>
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
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium text-gray-900">KES {{ number_format($creditNote['subtotal'] ?? 0, 2) }}</span>
                        </div>
                        @if(($creditNote['vat_amount'] ?? 0) > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">VAT (16%):</span>
                                <span class="font-medium text-gray-900">KES {{ number_format($creditNote['vat_amount'], 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2">
                            <span class="text-gray-900">Total Credit:</span>
                            <span class="text-gray-900">KES {{ number_format($creditNote['total_credit'] ?? 0, 2) }}</span>
                        </div>
                        @if(($creditNote['applied_amount'] ?? 0) > 0)
                            <div class="flex justify-between text-sm text-green-600">
                                <span>Applied:</span>
                                <span class="font-medium">KES {{ number_format($creditNote['applied_amount'], 2) }}</span>
                            </div>
                        @endif
                        @if(($creditNote['remaining_credit'] ?? 0) > 0)
                            <div class="flex justify-between text-sm text-blue-600">
                                <span>Remaining:</span>
                                <span class="font-medium">KES {{ number_format($creditNote['remaining_credit'], 2) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if(isset($creditNote['notes']) && $creditNote['notes'])
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Notes</h3>
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $creditNote['notes'] }}</p>
                </div>
            @endif
        </div>
    </x-card>
</div>

<!-- Apply to Invoice Modal -->
@if(($creditNote['can_apply_to_invoice'] ?? false) && isset($availableInvoices) && count($availableInvoices) > 0)
    <div id="apply-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Apply Credit Note to Invoice</h3>
                <button onclick="document.getElementById('apply-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="{{ route('user.credit-notes.apply-to-invoice', $creditNote['id']) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Invoice</label>
                    <select name="invoice_id" class="w-full rounded-lg border-gray-300" required>
                        <option value="">Select an invoice...</option>
                        @foreach($availableInvoices as $invoice)
                            <option value="{{ $invoice['id'] }}">
                                {{ $invoice['invoice_number'] }} - KES {{ number_format($invoice['remaining'], 2) }} remaining
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-gray-500">
                        Available credit: KES {{ number_format($creditNote['remaining_credit'] ?? 0, 2) }}
                    </p>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('apply-modal').classList.add('hidden')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Apply Credit
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection

