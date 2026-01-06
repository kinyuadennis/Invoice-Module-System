@extends('user.onboarding.layout')

@section('title', 'Welcome')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-8 md:p-12">
    <div class="text-center">
        <!-- Welcome Icon -->
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-[#2B6EF6] bg-opacity-10 mb-6">
            <svg class="h-8 w-8 text-[#2B6EF6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-4">Welcome to InvoiceHub!</h1>
        <p class="text-lg text-gray-600 dark:text-gray-300 mb-8">
            We're excited to have you here. Let's get your account set up in just a few simple steps.
        </p>

        <div class="space-y-4 mb-8 text-left">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <div>
                    <h3 class="font-semibold text-gray-900">Set up your company profile</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Add your business details, logo, and branding</p>
                </div>
            </div>
            <div class="flex items-start">
                <svg class="h-6 w-6 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <div>
                    <h3 class="font-semibold text-gray-900">Configure invoice preferences</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Choose templates, prefixes, and default settings</p>
                </div>
            </div>
            <div class="flex items-start">
                <svg class="h-6 w-6 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <div>
                    <h3 class="font-semibold text-gray-900">Ensure eTIMS compliance</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Verify your KRA PIN for tax compliance</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('user.onboarding.store') }}" class="mt-8">
            @csrf
            <input type="hidden" name="step" value="1">
            <input type="hidden" name="action" value="next">
            
            <button type="submit" class="w-full md:w-auto px-8 py-3 bg-[#2B6EF6] text-white font-semibold rounded-lg hover:bg-[#2563EB] transition-colors duration-200 shadow-lg hover:shadow-xl">
                Get Started
            </button>
        </form>
    </div>
</div>
@endsection

