@extends('layouts.admin')

@section('title', 'Subscription Details')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Subscription Details</h1>
            <p class="mt-1 text-sm text-gray-600">Detailed subscription information</p>
        </div>
        <a href="{{ route('admin.billing.subscriptions') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
            Back to Subscriptions
        </a>
    </div>

    <!-- Subscription Info -->
    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">Company</h3>
                <p class="text-sm text-gray-900">
                    <a href="{{ route('admin.companies.show', $subscription->company_id) }}" class="text-indigo-600 hover:text-indigo-900">
                        {{ $subscription->company->name }}
                    </a>
                </p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">Plan</h3>
                <p class="text-sm text-gray-900">{{ $subscription->plan->name }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">Status</h3>
                <x-badge :variant="match($subscription->status) {
                    'active' => 'success',
                    'trial' => 'info',
                    'cancelled' => 'warning',
                    'expired' => 'danger',
                    default => 'default'
                }">{{ ucfirst($subscription->status) }}</x-badge>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">Start Date</h3>
                <p class="text-sm text-gray-900">{{ $subscription->starts_at->format('F d, Y H:i') }}</p>
            </div>

            @if($subscription->ends_at)
            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">End Date</h3>
                <p class="text-sm text-gray-900">{{ $subscription->ends_at->format('F d, Y H:i') }}</p>
            </div>
            @endif

            @if($subscription->trial_ends_at)
            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">Trial Ends</h3>
                <p class="text-sm text-gray-900">{{ $subscription->trial_ends_at->format('F d, Y H:i') }}</p>
            </div>
            @endif

            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">Auto Renew</h3>
                <p class="text-sm text-gray-900">{{ $subscription->auto_renew ? 'Yes' : 'No' }}</p>
            </div>

            @if($subscription->payment_method)
            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">Payment Method</h3>
                <p class="text-sm text-gray-900">{{ ucfirst($subscription->payment_method) }}</p>
            </div>
            @endif
        </div>
    </x-card>

    <!-- Billing History -->
    @if($subscription->billingHistory->count() > 0)
    <x-card padding="none">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Billing History</h2>
        </div>
        <x-table>
            <x-slot name="header">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                </tr>
            </x-slot>
            @foreach($subscription->billingHistory as $billing)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $billing->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $billing->currency }} {{ number_format($billing->amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <x-badge :variant="match($billing->status) {
                            'paid' => 'success',
                            'pending' => 'warning',
                            'failed' => 'danger',
                            default => 'default'
                        }">{{ ucfirst($billing->status) }}</x-badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $billing->transaction_id ?? 'N/A' }}
                    </td>
                </tr>
            @endforeach
        </x-table>
    </x-card>
    @endif
</div>
@endsection


