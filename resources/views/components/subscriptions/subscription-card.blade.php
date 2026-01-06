@props([
    'subscription',
    'showActions' => true,
    'size' => 'default', // 'default' | 'compact'
])

@php
$plan = $subscription->plan;
$isActive = $subscription->isActive();
$isPending = $subscription->isPending();
$isInGrace = $subscription->isInGrace();
$isExpired = $subscription->isExpired();
$isCancelled = $subscription->isCancelled();
@endphp

<x-card class="{{ $size === 'compact' ? 'p-4' : 'p-6' }}">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $plan?->name ?? 'Unknown Plan' }}
                </h3>
                <x-subscriptions.subscription-status-indicator :status="strtolower($subscription->status)" />
            </div>

            @if($plan?->description)
                <p class="text-sm text-gray-600 dark:text-gray-300 dark:text-gray-400 mb-4">
                    {{ $plan->description }}
                </p>
            @endif

            <div class="grid grid-cols-2 gap-4 {{ $size === 'compact' ? 'text-sm' : '' }}">
                @if($subscription->gateway)
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Payment Method</p>
                        <div class="flex items-center gap-2">
                            <x-payments.payment-method-icon :gateway="$subscription->gateway" size="sm" />
                            <span class="text-sm font-medium text-gray-900 dark:text-white uppercase">
                                {{ $subscription->gateway }}
                            </span>
                        </div>
                    </div>
                @endif

                @if($subscription->starts_at)
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Started</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $subscription->starts_at->format('M d, Y') }}
                        </p>
                    </div>
                @endif

                @if($subscription->next_billing_at)
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Next Billing</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $subscription->next_billing_at->format('M d, Y') }}
                        </p>
                    </div>
                @endif

                @if($plan?->price)
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Amount</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            <x-payments.payment-amount-display 
                                :amount="$plan->price" 
                                :currency="$plan->currency ?? 'KES'"
                                size="sm"
                            />
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                / {{ $plan->billing_period ?? 'month' }}
                            </span>
                        </p>
                    </div>
                @endif
            </div>
        </div>

        @if($showActions && $isActive && !$isCancelled)
            <div class="ml-4 flex flex-col gap-2">
                <form action="{{ route('user.subscriptions.cancel', $subscription) }}" method="POST" class="inline">
                    @csrf
                    <x-button 
                        variant="outline" 
                        size="sm"
                        onclick="return confirm('Are you sure you want to cancel this subscription?')"
                    >
                        Cancel
                    </x-button>
                </form>
            </div>
        @endif
    </div>
</x-card>

