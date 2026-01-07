@extends('layouts.user')

@section('title', 'Subscriptions')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">My Subscriptions</h1>
        <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Manage your subscription plans and billing</p>
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
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-12 text-center shadow-sm">
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-[#9A9A9A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No plans available</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-[#9A9A9A]">There are no subscription plans available at this time.</p>
    </div>
    @elseif($subscriptions->isEmpty())
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-8 mb-8 shadow-sm">
        <h2 class="text-xl font-black text-gray-900 dark:text-white mb-6 tracking-tight">Available Plans</h2>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($availablePlans as $plan)
            <div class="border border-gray-200 dark:border-[#333333] rounded-xl p-6 hover:border-blue-500/30 transition-all duration-200 bg-gray-50 dark:bg-white/[0.02]">
                <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">{{ $plan->name }}</h3>
                <p class="text-3xl font-black text-gray-900 dark:text-white mt-4 tracking-tight">
                    {{ $plan->currency ?? 'KES' }} {{ number_format($plan->price, 2) }}
                    <span class="text-sm font-bold text-gray-500 dark:text-[#9A9A9A]">
                        / {{ $plan->billing_period ?? 'month' }}
                    </span>
                </p>
                <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] mt-4 min-h-[3rem]">{{ $plan->description }}</p>
                <form action="{{ route('user.subscriptions.store') }}" method="POST" class="mt-6">
                    @csrf
                    <input type="hidden" name="subscription_plan_id" value="{{ $plan->id }}">
                    <button type="submit" class="w-full px-4 py-2.5 bg-indigo-600 text-white font-bold text-sm uppercase tracking-wider rounded-xl hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/20 transition-all">
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
            :show-actions="true" />
        @endforeach
    </div>
    @endif

    <!-- Payment History -->
    @if(isset($recentPayments) && $recentPayments->isNotEmpty())
    <div class="mt-8">
        <h2 class="text-xl font-black text-gray-900 dark:text-white mb-6 tracking-tight">Recent Payments</h2>
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Date</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Plan</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Amount</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Method</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-[#2A2A2A]">
                        @foreach($recentPayments as $payment)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                {{ $payment->payment_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                {{ $payment->payable->plan?->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <x-payments.payment-amount-display
                                    :amount="$payment->amount"
                                    :currency="$payment->payable->company?->currency ?? 'KES'" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <x-payments.payment-method-icon
                                    gateway="{{ $payment->gateway ?? 'stripe' }}"
                                    size="sm"
                                    :show-label="true" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <x-payments.payment-status-badge
                                    :status="strtolower($payment->status)"
                                    size="sm" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a
                                    href="{{ route('user.subscriptions.payment-status', $payment) }}"
                                    class="text-indigo-600 hover:text-indigo-900 font-bold text-xs uppercase tracking-wider">
                                    View Details
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection