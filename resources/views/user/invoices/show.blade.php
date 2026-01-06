@extends('layouts.user')

@section('title', 'Invoice Details')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Invoice {{ $invoice['invoice_number'] ?? 'INV-' . str_pad($invoice['id'], 3, '0', STR_PAD_LEFT) }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">View and manage invoice details</p>
        </div>
        <div class="flex items-center space-x-3 flex-wrap gap-2">
            <!-- Primary Actions -->
            <a href="{{ route('user.invoices.pdf', $invoice['id']) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Download PDF
            </a>

            <!-- Status-based Actions -->
            @if(($invoice['status'] ?? 'draft') === 'draft')
            <a href="{{ route('user.invoices.edit', $invoice['id']) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
            @if(isset($invoice['client']) && isset($invoice['client']['email']))
            <button
                onclick="sendInvoiceEmail({{ $invoice['id'] }})"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Send Invoice
            </button>
            @endif
            @elseif(($invoice['status'] ?? 'draft') === 'sent' || ($invoice['status'] ?? 'draft') === 'overdue')
            <button
                onclick="sendInvoiceEmail({{ $invoice['id'] }})"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                title="Resend invoice email">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Resend
            </button>
            <form method="POST" action="{{ route('user.invoices.update', $invoice['id']) }}" class="inline" onsubmit="return confirm('Mark this invoice as paid? This action cannot be easily undone.');">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="paid">
                <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Mark as Paid
                </button>
            </form>
            <form method="POST" action="{{ route('user.invoices.update', $invoice['id']) }}" class="inline">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="cancelled">
                <x-button type="submit" variant="outline" onclick="return confirm('Are you sure you want to cancel this invoice?')">
                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancel
                </x-button>
            </form>
            @endif

            <!-- Secondary Actions -->
            <form method="POST" action="{{ route('user.invoices.duplicate', $invoice['id']) }}" class="inline">
                @csrf
                <x-button type="submit" variant="outline" title="Create a copy of this invoice">
                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Duplicate
                </x-button>
            </form>
        </div>

        @push('scripts')
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            @if(config('services.stripe.key'))
            const stripe = Stripe('{{ config('
                services.stripe.key ') }}');
            @endif

            function sendInvoiceEmail(invoiceId) {
                if (!confirm('Send this invoice via email to the client?')) return;

                fetch(`/app/invoices/${invoiceId}/send-email`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Invoice sent successfully!');
                            window.location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to send invoice'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to send invoice. Please try again.');
                    });
            }

            function openRecordPaymentModal() {
                document.getElementById('record-payment-modal').classList.remove('hidden');
            }

            function closeRecordPaymentModal() {
                document.getElementById('record-payment-modal').classList.add('hidden');
                document.getElementById('record-payment-form').reset();
            }

            function openCreateRefundModal() {
                document.getElementById('create-refund-modal').classList.remove('hidden');
                loadRefundData();
            }

            function closeCreateRefundModal() {
                document.getElementById('create-refund-modal').classList.add('hidden');
                document.getElementById('create-refund-form').reset();
            }

            function loadRefundData() {
                const invoiceId = {
                    {
                        $invoice['id']
                    }
                };
                fetch(`/app/invoices/${invoiceId}/refunds`, {
                        headers: {
                            'Accept': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Populate payment dropdown if needed
                        const paymentSelect = document.getElementById('refund-payment-id');
                        if (paymentSelect && data.payments) {
                            paymentSelect.innerHTML = '<option value="">Refund against invoice (distribute across payments)</option>';
                            data.payments.forEach(payment => {
                                const available = payment.amount - (payment.refunded_amount || 0);
                                if (available > 0) {
                                    const option = document.createElement('option');
                                    option.value = payment.id;
                                    option.textContent = `Payment #${payment.id} - KES ${available.toFixed(2)} available`;
                                    paymentSelect.appendChild(option);
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error loading refund data:', error);
                    });
            }

            function createRefund(event) {
                event.preventDefault();

                const form = event.target;
                const formData = new FormData(form);
                const invoiceId = {
                    {
                        $invoice['id']
                    }
                };

                fetch(`/app/invoices/${invoiceId}/refunds`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Refund created successfully!');
                            window.location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to create refund'));
                            if (data.errors) {
                                console.error('Validation errors:', data.errors);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to create refund. Please try again.');
                    });
            }

            function processRefund(refundId) {
                if (!confirm('Process this refund? This will update payment records.')) return;

                fetch(`/app/refunds/${refundId}/process`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Refund processed successfully!');
                            window.location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to process refund'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to process refund. Please try again.');
                    });
            }

            function validateInvoiceForEtims(invoiceId) {
                fetch(`/app/invoices/${invoiceId}/etims/validate`, {
                        headers: {
                            'Accept': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.valid) {
                            alert('✓ Invoice is valid for eTIMS submission!');
                        } else {
                            let message = 'Validation failed:\n\n';
                            data.errors.forEach(error => {
                                message += '• ' + error + '\n';
                            });
                            alert(message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to validate invoice. Please try again.');
                    });
            }

            function recordPayment(event) {
                event.preventDefault();

                const form = event.target;
                const formData = new FormData(form);
                const invoiceId = {
                    {
                        $invoice['id']
                    }
                };

                fetch(`/app/invoices/${invoiceId}/record-payment`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Payment recorded successfully!');
                            window.location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to record payment'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to record payment. Please try again.');
                    });
            }

            @if(config('services.stripe.key'))

            function initiateStripePayment(invoiceId) {
                const button = document.getElementById('stripe-pay-button');
                if (!button) return;

                button.disabled = true;
                const originalHTML = button.innerHTML;
                button.innerHTML = 'Processing...';

                fetch(`/app/invoices/${invoiceId}/pay/stripe`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.client_secret) {
                            // Show payment modal
                            showStripePaymentModal(data.client_secret, invoiceId, button, originalHTML);
                        } else {
                            alert('Error: ' + (data.message || 'Failed to initiate payment'));
                            button.disabled = false;
                            button.innerHTML = originalHTML;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                        button.disabled = false;
                        button.innerHTML = originalHTML;
                    });
            }

            function showStripePaymentModal(clientSecret, invoiceId, button, originalHTML) {
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
                modal.id = 'stripe-payment-modal';
                modal.innerHTML = `
                <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                    <h3 class="text-lg font-semibold mb-4">Complete Payment</h3>
                    <div id="stripe-card-element" class="mb-4 p-3 border rounded"></div>
                    <div id="stripe-card-errors" class="text-red-600 text-sm mb-4"></div>
                    <div class="flex gap-2">
                        <button id="stripe-submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Pay Now</button>
                        <button onclick="document.getElementById('stripe-payment-modal').remove(); ${button ? `button.disabled = false; button.innerHTML = originalHTML;` : ''}" class="px-4 py-2 bg-gray-200 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                    </div>
                </div>
            `;
                document.body.appendChild(modal);

                const elements = stripe.elements();
                const cardElement = elements.create('card');
                cardElement.mount('#stripe-card-element');

                document.getElementById('stripe-submit').addEventListener('click', function() {
                    this.disabled = true;
                    this.textContent = 'Processing...';

                    stripe.confirmCardPayment(clientSecret, {
                        payment_method: {
                            card: cardElement,
                        }
                    }).then(function(result) {
                        if (result.error) {
                            document.getElementById('stripe-card-errors').textContent = result.error.message;
                            document.getElementById('stripe-submit').disabled = false;
                            document.getElementById('stripe-submit').textContent = 'Pay Now';
                        } else {
                            modal.remove();
                            alert('Payment successful!');
                            window.location.reload();
                        }
                    });
                });
            }
            @endif

            function initiateMpesaPayment(invoiceId) {
                const phoneNumber = prompt('Enter your M-Pesa phone number (e.g., 0712345678):');
                if (!phoneNumber) return;

                const button = document.getElementById('mpesa-pay-button');
                if (!button) return;

                button.disabled = true;
                const originalHTML = button.innerHTML;
                button.innerHTML = 'Sending STK Push...';

                fetch(`/app/invoices/${invoiceId}/pay/mpesa`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            phone_number: phoneNumber
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message || 'STK Push sent to your phone. Please complete the payment on your phone.');
                            // Poll for payment status
                            pollPaymentStatus(invoiceId);
                        } else {
                            alert('Error: ' + (data.message || 'Failed to initiate payment'));
                            button.disabled = false;
                            button.innerHTML = originalHTML;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                        button.disabled = false;
                        button.innerHTML = originalHTML;
                    });
            }

            function pollPaymentStatus(invoiceId) {
                let attempts = 0;
                const maxAttempts = 30; // Poll for 30 seconds

                const interval = setInterval(() => {
                    attempts++;
                    fetch(`/app/invoices/${invoiceId}/payment-status`, {
                            headers: {
                                'Accept': 'application/json',
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.payment && data.payment.gateway_status === 'completed') {
                                clearInterval(interval);
                                alert('Payment successful!');
                                window.location.reload();
                            } else if (attempts >= maxAttempts) {
                                clearInterval(interval);
                                alert('Payment is still processing. Please refresh the page to check status.');
                            }
                        })
                        .catch(error => {
                            console.error('Error checking payment status:', error);
                        });
                }, 1000); // Check every second
            }
        </script>
        @endpush
    </div>

    <!-- Invoice Details -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main Invoice Card -->
        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Invoice Details</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Invoice #{{ $invoice['invoice_number'] ?? 'INV-' . str_pad($invoice['id'], 3, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    @php
                    $statusService = new \App\Http\Services\InvoiceStatusService();
                    $invoiceStatus = $invoice['status'] ?? 'draft';
                    $statusVariant = $statusService::getStatusVariant($invoiceStatus);
                    $statusInfo = $statusService::getStatuses()[$invoiceStatus] ?? ['label' => ucfirst($invoiceStatus)];

                    // Create clear status labels
                    $statusLabels = [
                    'draft' => 'Draft (Editable)',
                    'sent' => 'Sent / Open',
                    'paid' => 'Paid',
                    'overdue' => 'Overdue',
                    'cancelled' => 'Cancelled',
                    ];
                    $displayLabel = $statusLabels[$invoiceStatus] ?? $statusInfo['label'] ?? ucfirst($invoiceStatus);
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <x-badge :variant="$statusVariant" title="{{ $statusInfo['description'] ?? '' }}">
                                {{ $displayLabel }}
                            </x-badge>
                            @if($invoiceStatus === 'overdue')
                            <span class="text-xs text-red-600 font-medium">
                                {{ \Carbon\Carbon::parse($invoice['due_date'])->diffForHumans() }}
                            </span>
                            @endif
                        </div>
                        @if($invoiceStatus === 'draft')
                        <span class="text-xs text-gray-500 italic">Fully editable</span>
                        @elseif(in_array($invoiceStatus, ['sent', 'overdue']))
                        <span class="text-xs text-gray-500 italic">Limited editing</span>
                        @elseif($invoiceStatus === 'paid')
                        <span class="text-xs text-green-600 font-medium">✓ Payment received</span>
                        @endif
                    </div>
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
                    <div class="text-sm text-gray-600 dark:text-gray-300">
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
                <div class="px-5 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Line Items</h2>
                </div>
                <x-table>
                    <x-slot name="header">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Line Total</th>
                        </tr>
                    </x-slot>
                    @foreach($invoice['items'] ?? [] as $item)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-900">{{ $item['description'] ?? '' }}</td>
                        <td class="px-5 py-3 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-300">{{ $item['quantity'] ?? 0 }}</td>
                        <td class="px-5 py-3 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-300">KES {{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                        <td class="px-5 py-3 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                            KES {{ number_format($item['total_price'] ?? $item['total'] ?? 0, 2) }}
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
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Subtotal</span>
                        <span class="font-medium text-gray-900">KES {{ number_format($invoice['subtotal'] ?? 0, 2) }}</span>
                    </div>
                    @if(($invoice['tax_rate'] ?? 0) > 0)
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Tax ({{ $invoice['tax_rate'] ?? 0 }}%)</span>
                        <span class="font-medium text-gray-900">KES {{ number_format($invoice['tax'] ?? 0, 2) }}</span>
                    </div>
                    @endif
                    @if(($invoice['platform_fee'] ?? 0) > 0)
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Platform Fee</span>
                        <span class="font-medium text-gray-900">KES {{ number_format($invoice['platform_fee'] ?? 0, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between pt-2 border-t border-gray-200 text-base font-semibold text-gray-900">
                        <span>Total</span>
                        <span class="text-indigo-600">KES {{ number_format($invoice['total'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </x-card>

            <!-- Notes -->
            @if(isset($invoice['notes']) && $invoice['notes'])
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Notes</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ $invoice['notes'] }}</p>
            </x-card>
            @endif

            <!-- Payment Summary & Recording -->
            @php
            $paymentSummary = $paymentSummary ?? null;
            $totalPaid = $paymentSummary['total_paid'] ?? 0;
            $invoiceTotal = $paymentSummary['invoice_total'] ?? ($invoice['grand_total'] ?? 0);
            $remaining = $paymentSummary['remaining'] ?? max(0, $invoiceTotal - $totalPaid);
            $isFullyPaid = $paymentSummary['is_fully_paid'] ?? ($totalPaid >= $invoiceTotal);
            @endphp

            <x-card>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Payment Summary</h2>
                    @if(!$isFullyPaid && ($invoice['status'] ?? 'draft') !== 'cancelled' && ($invoice['status'] ?? 'draft') !== 'draft')
                    <button
                        onclick="openRecordPaymentModal()"
                        class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Record Payment
                    </button>
                    @endif
                </div>

                <!-- Payment Gateway Buttons -->
                @if(!$isFullyPaid && ($invoice['status'] ?? 'draft') !== 'cancelled' && ($invoice['status'] ?? 'draft') !== 'draft')
                @php
                $stripeEnabled = config('services.stripe.key') && config('services.stripe.secret');
                $mpesaEnabled = config('services.mpesa.consumer_key') && config('services.mpesa.consumer_secret') && config('services.mpesa.shortcode') && config('services.mpesa.passkey');
                @endphp
                @if($stripeEnabled || $mpesaEnabled)
                <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Pay Online</h3>
                    <div class="space-y-2">
                        <!-- Stripe Payment Button -->
                        @if($stripeEnabled)
                        <button
                            id="stripe-pay-button"
                            onclick="initiateStripePayment({{ $invoice['id'] }})"
                            class="w-full px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l-2.788 6.916c-.767 1.456-1.915 2.178-3.424 2.178-.76 0-1.427-.18-1.988-.535l.576-4.415c.255-.921.394-1.31 1.424-1.661zm-2.543-4.94c-1.98-.81-3.356-1.9-3.356-3.282 0-1.044.911-1.528 2.125-1.528 1.667 0 3.376.858 4.536 1.631l-2.788 6.916c-.73 1.456-1.96 2.178-3.41 2.178-.76 0-1.427-.18-1.988-.535L5.149 4.21z" />
                            </svg>
                            Pay with Card (Stripe)
                        </button>
                        @endif

                        <!-- M-Pesa Payment Button -->
                        @if($mpesaEnabled)
                        <button
                            id="mpesa-pay-button"
                            onclick="initiateMpesaPayment({{ $invoice['id'] }})"
                            class="w-full px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                            </svg>
                            Pay with M-Pesa
                        </button>
                        @endif
                    </div>
                </div>
                @endif
                @endif

                <div class="space-y-4">
                    <!-- Payment Progress -->
                    <div>
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="text-gray-600 dark:text-gray-300">Total Paid</span>
                            <span class="font-semibold text-gray-900">KES {{ number_format($totalPaid, 2) }} / KES {{ number_format($invoiceTotal, 2) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all duration-300 {{ $isFullyPaid ? 'bg-green-500' : 'bg-blue-500' }}"
                                style="width: {{ $invoiceTotal > 0 ? min(100, ($totalPaid / $invoiceTotal) * 100) : 0 }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500 mt-1">
                            <span>{{ $paymentSummary['payment_percentage'] ?? 0 }}% paid</span>
                            @if(!$isFullyPaid)
                            <span class="text-red-600 font-medium">KES {{ number_format($remaining, 2) }} remaining</span>
                            @endif
                        </div>
                    </div>

                    <!-- Payment History -->
                    @if(isset($invoice['payments']) && count($invoice['payments']) > 0)
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Payment History</h3>
                        <div class="space-y-3">
                            @foreach($invoice['payments'] as $payment)
                            @php
                            $paymentAmount = (float) ($payment['amount'] ?? 0);
                            $refundedAmount = (float) ($payment['refunded_amount'] ?? 0);
                            $netAmount = $paymentAmount - $refundedAmount;
                            @endphp
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="font-medium text-gray-900">KES {{ number_format($paymentAmount, 2) }}</p>
                                        @if($refundedAmount > 0)
                                        <span class="text-xs text-red-600">(KES {{ number_format($refundedAmount, 2) }} refunded)</span>
                                        @endif
                                        @if(isset($payment['payment_method']))
                                        <span class="text-xs text-gray-500">via {{ $payment['payment_method'] }}</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                        <span>{{ $payment['payment_date'] ? \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y') : 'N/A' }}</span>
                                        @if(isset($payment['mpesa_reference']))
                                        <span>Ref: {{ $payment['mpesa_reference'] }}</span>
                                        @endif
                                        @if($refundedAmount > 0)
                                        <span class="text-red-600">Net: KES {{ number_format($netAmount, 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                                @php
                                $statusVariant = 'success';
                                if ($refundedAmount >= $paymentAmount) {
                                $statusVariant = 'default';
                                } elseif ($refundedAmount > 0) {
                                $statusVariant = 'warning';
                                }
                                @endphp
                                <x-badge :variant="$statusVariant">
                                    @if($refundedAmount >= $paymentAmount)
                                    Fully Refunded
                                    @elseif($refundedAmount > 0)
                                    Partially Refunded
                                    @else
                                    Paid
                                    @endif
                                </x-badge>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4 text-sm text-gray-500 border-t border-gray-200">
                        No payments recorded yet
                    </div>
                    @endif
                </div>
            </x-card>

            <!-- Refund Summary & History -->
            @php
            $refundSummary = $refundSummary ?? null;
            $totalRefunded = $refundSummary['total_refunded'] ?? 0;
            $pendingRefunds = $refundSummary['pending_refunds'] ?? 0;
            @endphp
            @if($totalRefunded > 0 || $pendingRefunds > 0 || (isset($invoice['refunds']) && count($invoice['refunds']) > 0))
            <x-card>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Refunds</h2>
                    @if($totalPaid > 0 && ($invoice['status'] ?? 'draft') !== 'cancelled' && ($invoice['status'] ?? 'draft') !== 'draft')
                    <button
                        onclick="openCreateRefundModal()"
                        class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Refund
                    </button>
                    @endif
                </div>

                @if($totalRefunded > 0 || $pendingRefunds > 0)
                <div class="mb-4 p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-red-900">Total Refunded</p>
                            <p class="text-2xl font-bold text-red-600">KES {{ number_format($totalRefunded, 2) }}</p>
                        </div>
                        @if($pendingRefunds > 0)
                        <div class="text-right">
                            <p class="text-sm font-medium text-orange-900">Pending</p>
                            <p class="text-xl font-bold text-orange-600">KES {{ number_format($pendingRefunds, 2) }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Refund History -->
                @if(isset($invoice['refunds']) && count($invoice['refunds']) > 0)
                <div class="border-t border-gray-200 pt-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Refund History</h3>
                    <div class="space-y-3">
                        @foreach($invoice['refunds'] as $refund)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <p class="font-medium text-gray-900">KES {{ number_format($refund['amount'] ?? 0, 2) }}</p>
                                    <span class="text-xs text-gray-500">({{ $refund['refund_reference'] ?? 'N/A' }})</span>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    <span>{{ $refund['refund_date'] ? \Carbon\Carbon::parse($refund['refund_date'])->format('M d, Y') : 'N/A' }}</span>
                                    @if(isset($refund['refund_method']))
                                    <span>via {{ $refund['refund_method'] }}</span>
                                    @endif
                                    @if(isset($refund['reason']))
                                    <span>• {{ $refund['reason'] }}</span>
                                    @endif
                                </div>
                            </div>
                            @php
                            $refundStatus = $refund['status'] ?? 'pending';
                            $statusVariants = [
                            'processed' => 'success',
                            'pending' => 'warning',
                            'failed' => 'error',
                            'cancelled' => 'default',
                            ];
                            @endphp
                            <x-badge :variant="$statusVariants[$refundStatus] ?? 'default'">
                                {{ ucfirst($refundStatus) }}
                            </x-badge>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="text-center py-4 text-sm text-gray-500 border-t border-gray-200">
                    No refunds recorded yet
                </div>
                @endif
            </x-card>
            @endif

            <!-- History & Activity -->
            @if(isset($invoice['audit_logs']) && count($invoice['audit_logs']) > 0)
            <x-card>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">History & Activity</h2>
                </div>
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @foreach($invoice['audit_logs'] as $log)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-indigo-50 flex items-center justify-center ring-8 ring-white">
                                            <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                <span class="font-medium text-gray-900">{{ $log['user']['name'] ?? 'System' }}</span>
                                                {{ $log['description'] }}
                                            </p>
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                            <time datetime="{{ $log['created_at'] }}">{{ \Carbon\Carbon::parse($log['created_at'])->diffForHumans() }}</time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </x-card>
            @endif

            <!-- Record Payment Modal -->
            <div
                id="record-payment-modal"
                class="fixed inset-0 z-50 overflow-y-auto hidden"
                onclick="if(event.target === this) closeRecordPaymentModal()">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <form id="record-payment-form" onsubmit="recordPayment(event)">
                            @csrf
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Record Payment</h3>
                                    <button type="button" onclick="closeRecordPaymentModal()" class="text-gray-400 hover:text-gray-600 dark:text-gray-300">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Amount *</label>
                                        <input
                                            type="number"
                                            name="amount"
                                            step="0.01"
                                            min="0.01"
                                            max="{{ $remaining }}"
                                            value="{{ $remaining }}"
                                            required
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <p class="text-xs text-gray-500 mt-1">Remaining: KES {{ number_format($remaining, 2) }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Payment Date *</label>
                                        <input
                                            type="date"
                                            name="payment_date"
                                            value="{{ date('Y-m-d') }}"
                                            required
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Payment Method</label>
                                        <select
                                            name="payment_method"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Select method...</option>
                                            <option value="M-Pesa">M-Pesa</option>
                                            <option value="Bank Transfer">Bank Transfer</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Cheque">Cheque</option>
                                            <option value="Credit Card">Credit Card</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Reference Number</label>
                                        <input
                                            type="text"
                                            name="mpesa_reference"
                                            placeholder="e.g., M-Pesa transaction code"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button
                                    type="submit"
                                    class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Record Payment
                                </button>
                                <button
                                    type="button"
                                    onclick="closeRecordPaymentModal()"
                                    class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- eTIMS Compliance Section -->
            @if(($invoice['status'] ?? 'draft') !== 'draft' && ($invoice['status'] ?? 'draft') !== 'cancelled')
            <x-card>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">eTIMS Compliance</h2>
                    <div class="flex gap-2">
                        <button
                            onclick="validateInvoiceForEtims({{ $invoice['id'] }})"
                            class="px-4 py-2 text-sm font-medium text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-lg hover:bg-yellow-100">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Validate
                        </button>
                        @if(!isset($invoice['etims_control_number']) || empty($invoice['etims_control_number']))
                        <form method="POST" action="{{ route('user.invoices.etims.submit', $invoice['id']) }}" class="inline" onsubmit="return confirm('Submit this invoice to eTIMS?');">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                Submit to eTIMS
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('user.invoices.etims.export', ['invoice' => $invoice['id'], 'format' => 'json']) }}" class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export
                        </a>
                    </div>
                </div>

                @if(isset($invoice['etims_control_number']) && !empty($invoice['etims_control_number']))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-900">✓ Submitted to eTIMS</p>
                            <p class="text-xs text-green-700 mt-1">Control Number: {{ $invoice['etims_control_number'] }}</p>
                            @if(isset($invoice['etims_submitted_at']))
                            <p class="text-xs text-green-600 mt-1">Submitted: {{ \Carbon\Carbon::parse($invoice['etims_submitted_at'])->format('M d, Y H:i') }}</p>
                            @endif
                        </div>
                        @if(isset($invoice['etims_qr_code']) && !empty($invoice['etims_qr_code']))
                        <div class="bg-white p-2 rounded">
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(100)->generate($invoice['etims_qr_code']) !!}
                        </div>
                        @endif
                    </div>
                </div>
                @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-800">
                        <strong>Not yet submitted to eTIMS.</strong> Validate the invoice before submission to ensure compliance.
                    </p>
                </div>
                @endif
            </x-card>
            @endif

            <!-- Create Refund Modal -->
            <div
                id="create-refund-modal"
                class="fixed inset-0 z-50 overflow-y-auto hidden"
                onclick="if(event.target === this) closeCreateRefundModal()">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <form id="create-refund-form" onsubmit="createRefund(event)">
                            @csrf
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Create Refund</h3>
                                    <button type="button" onclick="closeCreateRefundModal()" class="text-gray-400 hover:text-gray-600 dark:text-gray-300">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Refund Amount *</label>
                                        <input
                                            type="number"
                                            name="amount"
                                            step="0.01"
                                            min="0.01"
                                            max="{{ $totalPaid - $totalRefunded }}"
                                            required
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                        <p class="text-xs text-gray-500 mt-1">Available to refund: KES {{ number_format($totalPaid - $totalRefunded, 2) }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Refund Date *</label>
                                        <input
                                            type="date"
                                            name="refund_date"
                                            value="{{ date('Y-m-d') }}"
                                            required
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Payment (Optional)</label>
                                        <select
                                            id="refund-payment-id"
                                            name="payment_id"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                            <option value="">Refund against invoice (distribute across payments)</option>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Leave empty to distribute refund across all payments</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Refund Method</label>
                                        <select
                                            name="refund_method"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                            <option value="">Select method...</option>
                                            <option value="Original Payment Method">Original Payment Method</option>
                                            <option value="Bank Transfer">Bank Transfer</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Cheque">Cheque</option>
                                            <option value="Credit Card">Credit Card</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Reason</label>
                                        <select
                                            name="reason"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                            <option value="">Select reason...</option>
                                            <option value="Customer Request">Customer Request</option>
                                            <option value="Duplicate Payment">Duplicate Payment</option>
                                            <option value="Service Not Provided">Service Not Provided</option>
                                            <option value="Invoice Error">Invoice Error</option>
                                            <option value="Cancelled Order">Cancelled Order</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Notes</label>
                                        <textarea
                                            name="notes"
                                            rows="3"
                                            placeholder="Additional notes about this refund..."
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>
                                    </div>

                                    <div class="flex items-center">
                                        <input
                                            type="checkbox"
                                            id="process-immediately"
                                            name="process_immediately"
                                            value="1"
                                            class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                        <label for="process-immediately" class="ml-2 text-sm text-gray-700 dark:text-gray-200">
                                            Process refund immediately
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button
                                    type="submit"
                                    class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Create Refund
                                </button>
                                <button
                                    type="button"
                                    onclick="closeCreateRefundModal()"
                                    class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection