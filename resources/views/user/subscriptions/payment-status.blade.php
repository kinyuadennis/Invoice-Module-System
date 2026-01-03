@extends('layouts.user')

@section('title', 'Payment Status')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div x-data="paymentStatusPolling({{ $payment->id }}, {{ $needsPolling ? 'true' : 'false' }})">
        <!-- Payment Status Card -->
        <x-card class="p-6 mb-6">
            <div class="text-center">
                <!-- Status Icon -->
                <div class="mb-6 flex justify-center">
                    <div x-show="status === 'success'" class="w-20 h-20 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                        <svg class="w-12 h-12 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div x-show="status === 'pending' || status === 'initiated'" class="w-20 h-20 rounded-full bg-yellow-100 dark:bg-yellow-900/20 flex items-center justify-center">
                        <x-shared.loading-spinner size="lg" color="text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <div x-show="status === 'failed' || status === 'timeout'" class="w-20 h-20 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center">
                        <svg class="w-12 h-12 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>

                <!-- Status Title -->
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2" x-text="statusTitle"></h1>
                <p class="text-lg text-gray-600 dark:text-gray-400 mb-6" x-text="statusMessage"></p>

                <!-- Payment Details -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 mb-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Payment Amount</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                <x-payments.payment-amount-display 
                                    :amount="$payment->amount" 
                                    :currency="$subscription?->company?->currency ?? 'KES'"
                                    size="xl"
                                />
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Payment Method</p>
                            <div class="flex items-center gap-2">
                                <x-payments.payment-method-icon 
                                    gateway="{{ $payment->gateway ?? 'stripe' }}" 
                                    size="md"
                                    :show-label="true"
                                />
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Transaction ID</p>
                            <p class="text-sm font-mono text-gray-900 dark:text-white">
                                {{ $payment->gateway_transaction_id ?? 'Pending...' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Status</p>
                            <x-payments.payment-status-badge 
                                :status="strtolower($payment->status)" 
                                size="md"
                            />
                        </div>
                    </div>
                </div>

                <!-- Subscription Details -->
                @if($subscription && $plan)
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Subscription Details</h3>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Plan</p>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Subscription Status</p>
                                <x-subscriptions.subscription-status-indicator 
                                    :status="strtolower($subscription->status)" 
                                    size="md"
                                />
                            </div>
                        </div>
                    </div>
                @endif

                <!-- M-Pesa Instructions (if pending) -->
                <div x-show="status === 'pending' || status === 'initiated'" x-cloak class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h4 class="font-semibold text-yellow-900 dark:text-yellow-200 mb-2">Complete Your Payment</h4>
                            <p class="text-sm text-yellow-800 dark:text-yellow-300 mb-3">
                                @if($payment->gateway === 'mpesa')
                                    Check your phone for an M-Pesa STK Push prompt. Enter your M-Pesa PIN to complete the payment.
                                @else
                                    Complete your payment using the secure payment form. Your payment is being processed.
                                @endif
                            </p>
                            <p class="text-xs text-yellow-700 dark:text-yellow-400">
                                This page will automatically update when your payment is confirmed.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                <div x-show="status === 'success'" x-cloak class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h4 class="font-semibold text-green-900 dark:text-green-200 mb-2">Payment Successful!</h4>
                            <p class="text-sm text-green-800 dark:text-green-300">
                                Your subscription has been activated. You can now access all premium features.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div x-show="status === 'failed' || status === 'timeout'" x-cloak class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h4 class="font-semibold text-red-900 dark:text-red-200 mb-2">Payment Failed</h4>
                            <p class="text-sm text-red-800 dark:text-red-300 mb-3" x-text="errorMessage"></p>
                            <div class="flex gap-3">
                                <x-button 
                                    variant="primary" 
                                    size="sm"
                                    href="{{ route('user.subscriptions.checkout', ['plan' => $plan?->id ?? '']) }}"
                                >
                                    Try Again
                                </x-button>
                                <x-button 
                                    variant="outline" 
                                    size="sm"
                                    href="{{ route('user.subscriptions.index') }}"
                                >
                                    View Subscriptions
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-center gap-4">
                    <x-button 
                        variant="primary" 
                        size="lg"
                        href="{{ route('user.subscriptions.success', ['payment' => $payment->id, 'subscription' => $subscription?->id]) }}"
                        x-show="status === 'success'"
                        x-cloak
                    >
                        View Success Page
                    </x-button>
                    <x-button 
                        variant="outline" 
                        size="lg"
                        href="{{ route('user.subscriptions.index') }}"
                        x-show="status === 'success'"
                        x-cloak
                    >
                        Go to Subscriptions
                    </x-button>
                </div>
            </div>
        </x-card>

        <!-- Polling Indicator -->
        <div x-show="polling && (status === 'pending' || status === 'initiated')" x-cloak class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                <x-shared.loading-spinner size="sm" color="text-gray-600 dark:text-gray-400" class="inline mr-2" />
                Checking payment status...
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
function paymentStatusPolling(paymentId, needsPolling) {
    return {
        paymentId: paymentId,
        status: '{{ strtolower($payment->status) }}',
        polling: needsPolling,
        pollInterval: null,
        pollCount: 0,
        maxPolls: 60, // Poll for up to 5 minutes (60 * 5 seconds)
        errorMessage: '',

        init() {
            if (this.polling && (this.status === 'pending' || this.status === 'initiated')) {
                this.startPolling();
            }
        },

        startPolling() {
            // Poll every 5 seconds
            this.pollInterval = setInterval(() => {
                this.checkStatus();
            }, 5000);

            // Also check immediately
            this.checkStatus();
        },

        async checkStatus() {
            if (this.pollCount >= this.maxPolls) {
                this.stopPolling();
                if (this.status === 'pending' || this.status === 'initiated') {
                    this.status = 'timeout';
                    this.errorMessage = 'Payment status check timed out. Please check your payment manually or contact support.';
                }
                return;
            }

            try {
                const response = await fetch(`/user/api/subscriptions/payment-status/${this.paymentId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Failed to check payment status');
                }

                const data = await response.json();
                this.status = data.status.toLowerCase();
                this.pollCount++;

                // Stop polling if payment is in terminal state
                if (data.is_terminal) {
                    this.stopPolling();
                    
                    // Redirect on success after a short delay
                    if (this.status === 'success') {
                        setTimeout(() => {
                            // Optionally redirect to success page
                            // window.location.href = '{{ route("user.subscriptions.index") }}';
                        }, 2000);
                    }
                }
            } catch (error) {
                console.error('Polling error:', error);
                // Continue polling on error (network issues, etc.)
            }
        },

        stopPolling() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
            this.polling = false;
        },

        get statusTitle() {
            const titles = {
                'success': 'Payment Successful!',
                'pending': 'Payment Pending',
                'initiated': 'Payment Initiated',
                'failed': 'Payment Failed',
                'timeout': 'Payment Timeout',
            };
            return titles[this.status] || 'Payment Status';
        },

        get statusMessage() {
            const messages = {
                'success': 'Your payment has been confirmed and your subscription is now active.',
                'pending': 'We are waiting for confirmation of your payment.',
                'initiated': 'Your payment request has been sent. Please complete the payment.',
                'failed': 'Unfortunately, your payment could not be processed.',
                'timeout': 'We were unable to confirm your payment status automatically.',
            };
            return messages[this.status] || 'Checking payment status...';
        },
    };
}
</script>
@endpush

