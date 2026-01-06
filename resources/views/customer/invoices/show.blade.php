<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Invoice #{{ $invoice->invoice_number ?? 'INV-'.str_pad($invoice->id, 3, '0', STR_PAD_LEFT) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Invoice #{{ $invoice->invoice_number ?? 'INV-'.str_pad($invoice->id, 3, '0', STR_PAD_LEFT) }}</h1>
                        <p class="text-gray-600 dark:text-gray-300 mt-1">From: {{ $invoice->company->name }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('customer.invoices.pdf', $accessToken->token) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download PDF
                        </a>
                        @php
                            $statusService = new \App\Http\Services\InvoiceStatusService();
                            $statusVariant = $statusService::getStatusVariant($invoice->status ?? 'draft');
                            $statusInfo = $statusService::getStatuses()[$invoice->status ?? 'draft'] ?? ['label' => ucfirst($invoice->status ?? 'draft')];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($statusVariant === 'success') bg-green-100 text-green-800
                            @elseif($statusVariant === 'warning') bg-yellow-100 text-yellow-800
                            @elseif($statusVariant === 'danger') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $statusInfo['label'] ?? ucfirst($invoice->status ?? 'draft') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Invoice Details -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Invoice Details</h2>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Issue Date</p>
                                <p class="font-medium text-gray-900">{{ $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('M d, Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Due Date</p>
                                <p class="font-medium text-gray-900">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bill To -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Bill To</h2>
                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            <p class="font-medium text-gray-900">{{ $invoice->client->name ?? 'Unknown Client' }}</p>
                            @if($invoice->client->email)
                                <p>{{ $invoice->client->email }}</p>
                            @endif
                            @if($invoice->client->address)
                                <p class="mt-1">{{ $invoice->client->address }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Line Items -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Line Items</h2>
                        </div>
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
                                    @foreach($invoice->items ?? [] as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['description'] ?? '' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-300">{{ $item['quantity'] ?? 0 }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-300">KES {{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                                KES {{ number_format($item['total_price'] ?? $item['total'] ?? 0, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Summary -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Summary</h2>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between text-gray-600 dark:text-gray-300">
                                <span>Subtotal</span>
                                <span class="font-medium text-gray-900">KES {{ number_format($invoice->subtotal ?? 0, 2) }}</span>
                            </div>
                            @if(($invoice->tax ?? $invoice->vat_amount ?? 0) > 0)
                                <div class="flex justify-between text-gray-600 dark:text-gray-300">
                                    <span>Tax</span>
                                    <span class="font-medium text-gray-900">KES {{ number_format($invoice->tax ?? $invoice->vat_amount ?? 0, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between pt-2 border-t border-gray-200 text-base font-semibold text-gray-900">
                                <span>Total</span>
                                <span class="text-indigo-600">KES {{ number_format($invoice->grand_total ?? $invoice->total ?? 0, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    @php
                        $totalPaid = $paymentSummary['total_paid'] ?? 0;
                        $invoiceTotal = $paymentSummary['invoice_total'] ?? ($invoice->grand_total ?? 0);
                        $remaining = $paymentSummary['remaining'] ?? max(0, $invoiceTotal - $totalPaid);
                        $isFullyPaid = $paymentSummary['is_fully_paid'] ?? ($totalPaid >= $invoiceTotal);
                    @endphp
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Summary</h2>
                        <div class="space-y-4">
                            <div>
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="text-gray-600 dark:text-gray-300">Total Paid</span>
                                    <span class="font-semibold text-gray-900">KES {{ number_format($totalPaid, 2) }} / KES {{ number_format($invoiceTotal, 2) }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-300 {{ $isFullyPaid ? 'bg-green-500' : 'bg-blue-500' }}"
                                         style="width: {{ min(100, ($totalPaid / max($invoiceTotal, 1)) * 100) }}%"></div>
                                </div>
                            </div>

                            @if(!$isFullyPaid && $invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                                <!-- Payment Gateway Buttons -->
                                @php
                                    $stripeEnabled = config('services.stripe.key') && config('services.stripe.secret');
                                    $mpesaEnabled = config('services.mpesa.consumer_key') && config('services.mpesa.consumer_secret') && config('services.mpesa.shortcode') && config('services.mpesa.passkey');
                                @endphp
                                @if($stripeEnabled || $mpesaEnabled)
                                    <div class="pt-4 border-t border-gray-200">
                                        <h3 class="text-sm font-medium text-gray-900 mb-3">Pay Online</h3>
                                        <div class="space-y-2">
                                            @if($stripeEnabled)
                                            <button 
                                                id="stripe-pay-button"
                                                onclick="initiateStripePayment('{{ $accessToken->token }}')"
                                                class="w-full px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2"
                                            >
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l-2.788 6.916c-.767 1.456-1.915 2.178-3.424 2.178-.76 0-1.427-.18-1.988-.535l.576-4.415c.255-.921.394-1.31 1.424-1.661zm-2.543-4.94c-1.98-.81-3.356-1.9-3.356-3.282 0-1.044.911-1.528 2.125-1.528 1.667 0 3.376.858 4.536 1.631l-2.788 6.916c-.73 1.456-1.96 2.178-3.41 2.178-.76 0-1.427-.18-1.988-.535L5.149 4.21z"/>
                                                </svg>
                                                Pay with Card (Stripe)
                                            </button>
                                            @endif

                                            @if($mpesaEnabled)
                                            <button 
                                                id="mpesa-pay-button"
                                                onclick="initiateMpesaPayment('{{ $accessToken->token }}')"
                                                class="w-full px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center gap-2"
                                            >
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                                </svg>
                                                Pay with M-Pesa
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($invoice->notes)
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-2">Notes</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ $invoice->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($stripeEnabled || $mpesaEnabled)
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        @if($stripeEnabled)
        const stripe = Stripe('{{ config('services.stripe.key') }}');

        function initiateStripePayment(token) {
            const button = document.getElementById('stripe-pay-button');
            button.disabled = true;
            button.textContent = 'Processing...';

            fetch(`/invoice/${token}/pay/stripe`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.client_secret) {
                    showStripePaymentModal(data.client_secret, token, button);
                } else {
                    alert('Error: ' + (data.message || 'Failed to initiate payment'));
                    button.disabled = false;
                    button.innerHTML = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l-2.788 6.916c-.767 1.456-1.915 2.178-3.424 2.178-.76 0-1.427-.18-1.988-.535l.576-4.415c.255-.921.394-1.31 1.424-1.661zm-2.543-4.94c-1.98-.81-3.356-1.9-3.356-3.282 0-1.044.911-1.528 2.125-1.528 1.667 0 3.376.858 4.536 1.631l-2.788 6.916c-.73 1.456-1.96 2.178-3.41 2.178-.76 0-1.427-.18-1.988-.535L5.149 4.21z"/></svg> Pay with Card (Stripe)';
                }
            });
        }

        function showStripePaymentModal(clientSecret, token, button) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
            modal.innerHTML = `
                <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                    <h3 class="text-lg font-semibold mb-4">Complete Payment</h3>
                    <div id="stripe-card-element" class="mb-4 p-3 border rounded"></div>
                    <div id="stripe-card-errors" class="text-red-600 text-sm mb-4"></div>
                    <div class="flex gap-2">
                        <button id="stripe-submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Pay Now</button>
                        <button onclick="this.closest('.fixed').remove(); button.disabled = false; button.innerHTML = '<svg class=\\'w-5 h-5\\' fill=\\'currentColor\\' viewBox=\\'0 0 24 24\\'><path d=\\'M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l-2.788 6.916c-.767 1.456-1.915 2.178-3.424 2.178-.76 0-1.427-.18-1.988-.535l.576-4.415c.255-.921.394-1.31 1.424-1.661zm-2.543-4.94c-1.98-.81-3.356-1.9-3.356-3.282 0-1.044.911-1.528 2.125-1.528 1.667 0 3.376.858 4.536 1.631l-2.788 6.916c-.73 1.456-1.96 2.178-3.41 2.178-.76 0-1.427-.18-1.988-.535L5.149 4.21z\\'/></svg> Pay with Card (Stripe)';" class="px-4 py-2 bg-gray-200 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
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
                    payment_method: { card: cardElement }
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

        function initiateMpesaPayment(token) {
            const phoneNumber = prompt('Enter your M-Pesa phone number (e.g., 0712345678):');
            if (!phoneNumber) return;

            const button = document.getElementById('mpesa-pay-button');
            button.disabled = true;
            button.textContent = 'Sending STK Push...';

            fetch(`/invoice/${token}/pay/mpesa`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ phone_number: phoneNumber }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'STK Push sent to your phone. Please complete the payment.');
                    pollPaymentStatus(token);
                } else {
                    alert('Error: ' + (data.message || 'Failed to initiate payment'));
                    button.disabled = false;
                    button.innerHTML = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg> Pay with M-Pesa';
                }
            });
        }

        function pollPaymentStatus(token) {
            let attempts = 0;
            const maxAttempts = 30;
            const interval = setInterval(() => {
                attempts++;
                fetch(`/invoice/${token}/payment-status`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.payment && data.payment.gateway_status === 'completed') {
                            clearInterval(interval);
                            alert('Payment successful!');
                            window.location.reload();
                        } else if (attempts >= maxAttempts) {
                            clearInterval(interval);
                            alert('Payment is still processing. Please refresh the page.');
                        }
                    });
            }, 1000);
        }
    </script>
    @endif
</body>
</html>

