@extends('layouts.admin')

@section('title', 'Subscription Plans')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Subscription Plans</h1>
            <p class="mt-1 text-sm text-gray-600">Manage subscription plans and pricing</p>
        </div>
    </div>

    @if($plans->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($plans as $plan)
                <x-card>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h3>
                        <p class="mt-2 text-sm text-gray-600">{{ $plan->description }}</p>
                        <div class="mt-4">
                            <span class="text-4xl font-bold text-gray-900">{{ $plan->currency }} {{ number_format($plan->price, 2) }}</span>
                            <span class="text-gray-600">/{{ $plan->billing_period }}</span>
                        </div>
                        <ul class="mt-6 space-y-2 text-left">
                            @if($plan->max_companies)
                                <li class="text-sm text-gray-600">✓ Up to {{ $plan->max_companies }} companies</li>
                            @else
                                <li class="text-sm text-gray-600">✓ Unlimited companies</li>
                            @endif
                            @if($plan->max_users_per_company)
                                <li class="text-sm text-gray-600">✓ {{ $plan->max_users_per_company }} users per company</li>
                            @endif
                            @if($plan->max_invoices_per_month)
                                <li class="text-sm text-gray-600">✓ {{ $plan->max_invoices_per_month }} invoices/month</li>
                            @endif
                            @if($plan->max_clients)
                                <li class="text-sm text-gray-600">✓ Up to {{ $plan->max_clients }} clients</li>
                            @endif
                            @if($plan->features)
                                @foreach($plan->features as $feature)
                                    <li class="text-sm text-gray-600">✓ {{ $feature }}</li>
                                @endforeach
                            @endif
                        </ul>
                        <div class="mt-6">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $plan->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>
    @else
        <x-card>
            <div class="text-center py-12">
                <p class="text-sm text-gray-500">No subscription plans configured yet</p>
            </div>
        </x-card>
    @endif
</div>
@endsection


