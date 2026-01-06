@extends('layouts.user')

@section('title', 'Subscriptions')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">My Subscriptions</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 dark:text-gray-400">Manage your subscription plans and billing</p>
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
            <p class="text-gray-600 dark:text-gray-300 dark:text-gray-400 mb-4">No subscription plans available at this time.</p>
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
                            <span class="text-sm font-normal text-gray-600 dark:text-gray-300 dark:text-gray-400">
                                / {{ $plan->billing_period ?? 'month' }}
                            </span>
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-300 dark:text-gray-400 mt-2">{{ $plan->description }}</p>
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
        <div class="grid gap-6 mb-8">
            @foreach($subscriptions as $subscription)
                <x-subscriptions.subscription-card 
                    :subscription="$subscription"
                    :show-actions="true"
                />
            @endforeach
        </div>
    @endif

    <!-- Payment History -->
    @if(isset($recentPayments) && $recentPayments->isNotEmpty())
        <div class="mt-8">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Recent Payments</h2>
            <x-card class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Plan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($recentPayments as $payment)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $payment->payment_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $payment->payable->plan?->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        <x-payments.payment-amount-display 
                                            :amount="$payment->amount" 
                                            :currency="$payment->payable->company?->currency ?? 'KES'"
                                        />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        <x-payments.payment-method-icon 
                                            gateway="{{ $payment->gateway ?? 'stripe' }}" 
                                            size="sm"
                                            :show-label="true"
                                        />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <x-payments.payment-status-badge 
                                            :status="strtolower($payment->status)" 
                                            size="sm"
                                        />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a 
                                            href="{{ route('user.subscriptions.payment-status', $payment) }}"
                                            class="text-[#2B6EF6] hover:underline"
                                        >
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    @endif
</div>
@endsection

