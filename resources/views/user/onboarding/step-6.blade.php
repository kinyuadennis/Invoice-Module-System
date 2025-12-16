@extends('user.onboarding.layout')

@section('title', 'Complete Setup')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-8 md:p-12">
    <div class="text-center mb-8">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">You're All Set!</h1>
        <p class="text-gray-600">Your account is ready to use. Let's verify your eTIMS compliance status.</p>
    </div>

    @if(isset($company) && $company->kra_pin)
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-green-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="font-semibold text-green-900 mb-1">eTIMS Ready</h3>
                    <p class="text-sm text-green-800">
                        Your KRA PIN ({{ $company->kra_pin }}) is configured. Your invoices will be eTIMS compliant.
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-yellow-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <h3 class="font-semibold text-yellow-900 mb-1">eTIMS Not Configured</h3>
                    <p class="text-sm text-yellow-800 mb-3">
                        You haven't added a KRA PIN yet. You can add it later in company settings to enable eTIMS compliance.
                    </p>
                    <a href="{{ route('user.company.settings') }}" class="text-sm font-medium text-yellow-900 hover:text-yellow-800 underline">
                        Add KRA PIN now →
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="space-y-4 mb-8">
        <h3 class="font-semibold text-gray-900">What's Next?</h3>
        <div class="space-y-3">
            <div class="flex items-start">
                <svg class="h-5 w-5 text-[#2B6EF6] mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-gray-900">Create your first invoice</p>
                    <p class="text-xs text-gray-600">Start invoicing your clients right away</p>
                </div>
            </div>
            <div class="flex items-start">
                <svg class="h-5 w-5 text-[#2B6EF6] mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-gray-900">Add your clients</p>
                    <p class="text-xs text-gray-600">Manage your client database</p>
                </div>
            </div>
            <div class="flex items-start">
                <svg class="h-5 w-5 text-[#2B6EF6] mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-gray-900">Customize your settings</p>
                    <p class="text-xs text-gray-600">Fine-tune your company preferences</p>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('user.onboarding.complete') }}" class="pt-4">
        @csrf
        <button type="submit" class="w-full px-6 py-3 bg-[#2B6EF6] text-white font-semibold rounded-lg hover:bg-[#2563EB] transition-colors shadow-lg hover:shadow-xl">
            Complete Setup & Go to Dashboard
        </button>
    </form>
</div>
@endsection

