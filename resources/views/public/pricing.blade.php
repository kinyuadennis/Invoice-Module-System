@extends('layouts.public')

@section('title', 'Pricing')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center">
        <h1 class="text-4xl font-extrabold text-gray-900">Simple, Transparent Pricing</h1>
        <p class="mt-4 text-xl text-gray-600">Choose the plan that works for you</p>
    </div>

    <div class="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-3">
        <x-card>
            <h3 class="text-2xl font-bold text-gray-900">Free</h3>
            <p class="mt-4 text-4xl font-extrabold text-gray-900">$0<span class="text-base font-normal text-gray-500">/month</span></p>
            <ul class="mt-6 space-y-4">
                <li class="flex items-start">
                    <svg class="flex-shrink-0 h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="ml-3 text-gray-600">Up to 10 invoices/month</span>
                </li>
                <li class="flex items-start">
                    <svg class="flex-shrink-0 h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="ml-3 text-gray-600">Basic client management</span>
                </li>
            </ul>
        </x-card>

        <x-card>
            <h3 class="text-2xl font-bold text-gray-900">Pro</h3>
            <p class="mt-4 text-4xl font-extrabold text-gray-900">$29<span class="text-base font-normal text-gray-500">/month</span></p>
            <ul class="mt-6 space-y-4">
                <li class="flex items-start">
                    <svg class="flex-shrink-0 h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="ml-3 text-gray-600">Unlimited invoices</span>
                </li>
                <li class="flex items-start">
                    <svg class="flex-shrink-0 h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="ml-3 text-gray-600">Advanced reporting</span>
                </li>
            </ul>
        </x-card>

        <x-card>
            <h3 class="text-2xl font-bold text-gray-900">Enterprise</h3>
            <p class="mt-4 text-4xl font-extrabold text-gray-900">Custom</p>
            <ul class="mt-6 space-y-4">
                <li class="flex items-start">
                    <svg class="flex-shrink-0 h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="ml-3 text-gray-600">Everything in Pro</span>
                </li>
                <li class="flex items-start">
                    <svg class="flex-shrink-0 h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="ml-3 text-gray-600">Dedicated support</span>
                </li>
            </ul>
        </x-card>
    </div>
</div>
@endsection

