@extends('layouts.user')

@section('title', 'Payment Successful')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="successPage()" x-init="init()">
    <!-- Confetti Container -->
    <div id="confetti-container" class="fixed inset-0 pointer-events-none z-50"></div>

    <!-- Success Card -->
    <x-card class="p-8 text-center">
        <!-- Success Icon with Animation -->
        <div class="mb-6 flex justify-center">
            <div class="w-24 h-24 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center animate-scale-in">
                <svg class="w-16 h-16 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <!-- Success Title -->
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
            Payment Successful! 🎉
        </h1>
        <p class="text-xl text-gray-600 dark:text-gray-400 mb-8">
            Your subscription has been activated successfully
        </p>

        <!-- Subscription Details -->
        @if($subscription && $plan)
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Subscription Details</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="text-left">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Plan</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $plan->name }}</p>
                    </div>
                    <div class="text-left">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Status</p>
                        <x-subscriptions.subscription-status-indicator 
                            :status="strtolower($subscription->status)" 
                            size="md"
                        />
                    </div>
                    @if($payment)
                        <div class="text-left">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Amount Paid</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">
                                <x-payments.payment-amount-display 
                                    :amount="$payment->amount" 
                                    :currency="$subscription->company?->currency ?? 'KES'"
                                    size="lg"
                                />
                            </p>
                        </div>
                        <div class="text-left">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Payment Method</p>
                            <div class="flex items-center gap-2">
                                <x-payments.payment-method-icon 
                                    gateway="{{ $payment->gateway ?? 'stripe' }}" 
                                    size="md"
                                    :show-label="true"
                                />
                            </div>
                        </div>
                    @endif
                    @if($subscription->next_billing_at)
                        <div class="text-left md:col-span-2">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Next Billing Date</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $subscription->next_billing_at->format('F d, Y') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- What's Next Section -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 mb-8 text-left">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">What's Next?</h3>
            <ul class="space-y-3">
                <li class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-[#2B6EF6] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Access Premium Features</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">You now have access to all premium features including unlimited invoices, M-Pesa integration, and more.</p>
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-[#2B6EF6] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Create Your First Invoice</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Start invoicing your clients right away with our professional invoice templates.</p>
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-[#2B6EF6] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Set Up M-Pesa Payments</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Configure M-Pesa integration to accept payments directly from your invoices.</p>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <x-button 
                variant="primary" 
                size="lg"
                href="{{ route('user.invoices.create') }}"
            >
                Create First Invoice
            </x-button>
            <x-button 
                variant="outline" 
                size="lg"
                href="{{ route('user.subscriptions.index') }}"
            >
                View Subscription
            </x-button>
            <x-button 
                variant="ghost" 
                size="lg"
                href="{{ route('user.dashboard') }}"
            >
                Go to Dashboard
            </x-button>
        </div>

        <!-- Receipt Download & Social Sharing -->
        @if($payment)
            <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Need a receipt? Download your payment confirmation.
                </p>
                <div class="flex flex-wrap gap-3 justify-center">
                    <x-button 
                        variant="outline" 
                        size="md"
                        href="{{ route('user.payments.show', $payment) }}"
                    >
                        <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download Receipt
                    </x-button>
                </div>
            </div>
        @endif

        <!-- Social Sharing -->
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 text-center">
                Share your success with others!
            </p>
            <div class="flex justify-center gap-3">
                <a 
                    href="https://twitter.com/intent/tweet?text={{ urlencode('I just upgraded to InvoiceHub Premium! 🎉 Best invoicing solution for Kenyan businesses.') }}&url={{ urlencode(route('home')) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-4 py-2 bg-[#1DA1F2] text-white rounded-lg hover:bg-[#1a8cd8] transition-colors"
                >
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/>
                    </svg>
                    Share on Twitter
                </a>
                <a 
                    href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('home')) }}&quote={{ urlencode('I just upgraded to InvoiceHub Premium! 🎉') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-4 py-2 bg-[#1877F2] text-white rounded-lg hover:bg-[#166fe5] transition-colors"
                >
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    Share on Facebook
                </a>
                <a 
                    href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('home')) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-4 py-2 bg-[#0077B5] text-white rounded-lg hover:bg-[#006399] transition-colors"
                >
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                    Share on LinkedIn
                </a>
            </div>
        </div>
    </x-card>
</div>

@push('scripts')
<script>
function successPage() {
    return {
        confettiActive: false,

        init() {
            // Trigger confetti animation
            this.triggerConfetti();
        },

        triggerConfetti() {
            if (this.confettiActive) return;
            this.confettiActive = true;

            const container = document.getElementById('confetti-container');
            const colors = ['#2B6EF6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'];
            const confettiCount = 100;

            for (let i = 0; i < confettiCount; i++) {
                setTimeout(() => {
                    this.createConfetti(container, colors);
                }, i * 10);
            }

            // Clean up after animation
            setTimeout(() => {
                container.innerHTML = '';
                this.confettiActive = false;
            }, 5000);
        },

        createConfetti(container, colors) {
            const confetti = document.createElement('div');
            const color = colors[Math.floor(Math.random() * colors.length)];
            const size = Math.random() * 10 + 5;
            const startX = Math.random() * window.innerWidth;
            const duration = Math.random() * 3 + 2;

            confetti.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                background-color: ${color};
                left: ${startX}px;
                top: -10px;
                border-radius: ${Math.random() > 0.5 ? '50%' : '0'};
                opacity: ${Math.random() * 0.5 + 0.5};
                animation: confetti-fall ${duration}s linear forwards;
                transform: rotate(${Math.random() * 360}deg);
            `;

            container.appendChild(confetti);

            // Remove after animation
            setTimeout(() => {
                confetti.remove();
            }, duration * 1000);
        },
    };
}

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes confetti-fall {
        to {
            transform: translateY(100vh) rotate(720deg);
            opacity: 0;
        }
    }

    @keyframes animate-scale-in {
        from {
            transform: scale(0);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .animate-scale-in {
        animation: animate-scale-in 0.5s ease-out;
    }
`;
document.head.appendChild(style);
</script>
@endpush

