@extends('layouts.user')

@section('title', 'Subscriptions')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">My Subscriptions</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Manage your subscription plans and billing</p>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($subscriptions->isEmpty() && $availablePlans->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-8 text-center">
            <p class="text-gray-600 dark:text-gray-400 mb-4">No subscription plans available at this time.</p>
        </div>
    @elseif($subscriptions->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-8 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Available Plans</h2>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach($availablePlans as $plan)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">
                            {{ $plan->currency ?? 'KES' }} {{ number_format($plan->price, 2) }}
                            <span class="text-sm font-normal text-gray-600 dark:text-gray-400">
                                / {{ $plan->billing_period ?? 'month' }}
                            </span>
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $plan->description }}</p>
                        <form action="{{ route('user.subscriptions.store') }}" method="POST" class="mt-4">
                            @csrf
                            <input type="hidden" name="subscription_plan_id" value="{{ $plan->id }}">
                            <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Subscribe
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(!$subscriptions->isEmpty())
        <div class="grid gap-6">
            @foreach($subscriptions as $subscription)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $subscription->plan?->name ?? 'Unknown Plan' }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ $subscription->plan?->description ?? '' }}
                            </p>

                            <div class="mt-4 grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($subscription->isActive()) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($subscription->isPending()) bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @elseif($subscription->isInGrace()) bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                            @elseif($subscription->isExpired()) bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                            @endif">
                                            {{ $subscription->status }}
                                        </span>
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Gateway</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white uppercase">
                                        {{ $subscription->gateway ?? 'N/A' }}
                                    </p>
                                </div>

                                @if($subscription->starts_at)
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Started</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $subscription->starts_at->format('M d, Y') }}
                                    </p>
                                </div>
                                @endif

                                @if($subscription->next_billing_at)
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Next Billing</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $subscription->next_billing_at->format('M d, Y') }}
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="ml-4">
                            @if($subscription->isActive() && !$subscription->isCancelled())
                                <form action="{{ route('user.subscriptions.cancel', $subscription) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
                                        onclick="return confirm('Are you sure you want to cancel this subscription?')">
                                        Cancel Subscription
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

