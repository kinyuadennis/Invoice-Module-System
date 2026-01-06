@extends('layouts.user')

@section('title', 'Payment Status')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div x-data="paymentStatusPolling({{ $payment->id }}, {{ $needsPolling ? 'true' : 'false' }}, '{{ $subscription?->stripe_id ?? '' }}')">
        <!-- Payment Status Card -->
        <x-card class="p-8 mb-6">
            <div class="text-center mb-8">
                <!-- Status Icon -->
                <div class="mb-6 flex justify-center">
                    <div x-show="status === 'success'" class="w-24 h-24 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center shadow-lg">
                        <svg class="w-14 h-14 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div x-show="status === 'pending' || status === 'initiated'" class="w-24 h-24 rounded-full bg-yellow-100 dark:bg-yellow-900/20 flex items-center justify-center shadow-lg">
                        <x-shared.loading-spinner size="lg" color="text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <div x-show="status === 'failed' || status === 'timeout'" class="w-24 h-24 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center shadow-lg">
                        <svg class="w-14 h-14 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>

                <!-- Status Title -->
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-3" x-text="statusTitle"></h1>
                <p class="text-xl text-gray-600 dark:text-gray-300 dark:text-gray-400 mb-8" x-text="statusMessage"></p>

                <!-- Payment Details -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-xl p-8 mb-8 border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 text-left">Payment Details</h2>
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Payment Amount</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">
                                <x-payments.payment-amount-display 
                                    :amount="$payment->amount" 
                                    :currency="$subscription?->company?->currency ?? 'KES'"
                                    size="xl"
                                />
                            </p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Payment Method</p>
                            <div class="flex items-center gap-3">
                                <x-payments.payment-method-icon 
                                    gateway="{{ $payment->gateway ?? 'stripe' }}" 
                                    size="md"
                                    :show-label="true"
                                />
                            </div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                                @if($payment->gateway === 'stripe' && $subscription?->stripe_id)
                                    Subscription ID
                                @else
                                    Transaction ID
                                @endif
                            </p>
                            <p class="text-sm font-mono text-gray-900 dark:text-white break-all">
                                @if($payment->gateway === 'stripe' && $subscription?->stripe_id)
                                    {{ $subscription->stripe_id }}
                                @else
                                    {{ $payment->gateway_transaction_id ?? 'Pending...' }}
                                @endif
                            </p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Status</p>
                            <x-payments.payment-status-badge 
                                :status="strtolower($payment->status)" 
                                size="md"
                            />
                        </div>
                    </div>
                </div>

                <!-- Subscription Details -->
                @if($subscription && $plan)
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-8 mb-8 border border-blue-200 dark:border-blue-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Subscription Details</h3>
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Plan</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $plan->name }}</p>
                            </div>
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Subscription Status</p>
                                <x-subscriptions.subscription-status-indicator 
                                    :status="strtolower($subscription->status)" 
                                    size="md"
                                />
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Payment Instructions (if pending) -->
                <div x-show="status === 'pending' || status === 'initiated'" x-cloak class="bg-gradient-to-br from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 border-2 border-yellow-300 dark:border-yellow-700 rounded-xl p-6 mb-8">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-yellow-100 dark:bg-yellow-900/40 flex items-center justify-center">
                                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-yellow-900 dark:text-yellow-200 mb-3">Complete Your Payment</h4>
                            <p class="text-sm text-yellow-800 dark:text-yellow-300 mb-3 leading-relaxed">
                                @if($payment->gateway === 'mpesa')
                                    Check your phone for an M-Pesa STK Push prompt. Enter your M-Pesa PIN to complete the payment.
                                @else
                                    Your payment is being processed. For Stripe subscriptions, the payment will be confirmed automatically once the subscription is activated.
                                @endif
                            </p>
                            <p class="text-xs text-yellow-700 dark:text-yellow-400 font-medium">
                                This page will automatically update when your payment is confirmed.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                <div x-show="status === 'success'" x-cloak class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-2 border-green-300 dark:border-green-700 rounded-xl p-6 mb-8">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-green-900 dark:text-green-200 mb-2">Payment Successful!</h4>
                            <p class="text-sm text-green-800 dark:text-green-300 leading-relaxed">
                                Your subscription has been activated. You can now access all premium features.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div x-show="status === 'failed' || status === 'timeout'" x-cloak class="bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 border-2 border-red-300 dark:border-red-700 rounded-xl p-6 mb-8">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-red-900 dark:text-red-200 mb-2">Payment Failed</h4>
                            <p class="text-sm text-red-800 dark:text-red-300 mb-4 leading-relaxed" x-text="errorMessage"></p>
                            <div class="flex flex-wrap gap-3">
                                <x-button 
                                    variant="primary" 
                                    size="md"
                                    href="{{ route('user.subscriptions.checkout', ['plan' => $plan?->id ?? '']) }}"
                                >
                                    Try Again
                                </x-button>
                                <x-button 
                                    variant="outline" 
                                    size="md"
                                    href="{{ route('user.subscriptions.index') }}"
                                >
                                    View Subscriptions
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div x-show="status === 'success'" x-cloak class="flex flex-col sm:flex-row justify-center gap-4 mt-8">
                    <x-button 
                        variant="primary" 
                        size="lg"
                        href="{{ route('user.subscriptions.success', ['payment' => $payment->id, 'subscription' => $subscription?->id]) }}"
                    >
                        View Success Page
                    </x-button>
                    <x-button 
                        variant="outline" 
                        size="lg"
                        href="{{ route('user.subscriptions.index') }}"
                    >
                        Go to Subscriptions
                    </x-button>
                    <x-button 
                        variant="ghost" 
                        size="lg"
                        href="{{ route('user.dashboard') }}"
                    >
                        Go to Dashboard
                    </x-button>
                </div>
            </div>
        </x-card>

        <!-- Polling Indicator -->
        <div x-show="polling && (status === 'pending' || status === 'initiated')" x-cloak class="text-center mt-6">
            <div class="inline-flex items-center gap-3 px-4 py-3 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <x-shared.loading-spinner size="sm" color="text-gray-600 dark:text-gray-300 dark:text-gray-400" />
                <p class="text-sm font-medium text-gray-700 dark:text-gray-200 dark:text-gray-300">
                    Checking payment status...
                </p>
                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="`(${pollCount}/${maxPolls})`"></span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function paymentStatusPolling(paymentId, needsPolling, stripeSubscriptionId = '') {
    return {
        paymentId: paymentId,
        status: '{{ strtolower($payment->status) }}',
        subscriptionStatus: '{{ strtolower($subscription?->status ?? '') }}',
        polling: needsPolling,
        pollInterval: null,
        pollCount: 0,
        maxPolls: 60, // Poll for up to 10 minutes (60 * 10 seconds)
        errorMessage: '',
        stripeSubscriptionId: stripeSubscriptionId,
        redirectCountdown: 5,

        init() {
            // Enable polling for both M-Pesa and Stripe if payment is not in terminal state
            if (this.polling && (this.status === 'pending' || this.status === 'initiated')) {
                this.startPolling();
            }
            // Also enable polling for Stripe if subscription is not active yet
            if (!this.polling && '{{ $payment->gateway }}' === 'stripe' && this.subscriptionStatus !== 'active' && (this.status === 'pending' || this.status === 'initiated')) {
                this.polling = true;
                this.startPolling();
            }
        },

        startPolling() {
            // Poll every 10 seconds
            this.pollInterval = setInterval(() => {
                this.checkStatus();
            }, 10000);

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
                
                // Update subscription status if provided
                if (data.subscription_status) {
                    this.subscriptionStatus = data.subscription_status.toLowerCase();
                }
                
                // Update Stripe subscription ID if provided
                if (data.stripe_subscription_id) {
                    this.stripeSubscriptionId = data.stripe_subscription_id;
                }
                
                this.pollCount++;

                // Stop polling if payment is in terminal state OR subscription is active
                if (data.is_terminal || this.subscriptionStatus === 'active') {
                    this.stopPolling();
                    
                    // Redirect on success after a countdown
                    if (this.status === 'success' || this.subscriptionStatus === 'active') {
                        this.startRedirectCountdown();
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

        startRedirectCountdown() {
            const countdownInterval = setInterval(() => {
                this.redirectCountdown--;
                if (this.redirectCountdown <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = '{{ route("user.subscriptions.success", ["payment" => $payment->id, "subscription" => $subscription?->id]) }}';
                }
            }, 1000);
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

<!-- Success Redirect Countdown -->
<div x-show="status === 'success' && redirectCountdown > 0" x-cloak class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg z-50">
    <p class="text-sm font-medium">
        Redirecting in <span x-text="redirectCountdown"></span> second(s)...
    </p>
</div>

