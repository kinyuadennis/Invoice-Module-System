@extends('layouts.public')

@section('title', 'Verify Email')
@section('heading', 'Verify Your Email Address')

@section('content')
<div class="max-w-md mx-auto" x-data="{ 
    checking: false, 
    verified: {{ $user->hasVerifiedEmail() ? 'true' : 'false' }},
    checkVerification() {
        this.checking = true;
        fetch('{{ route('verification.check') }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        })
        .then(response => response.json())
        .then(data => {
            if (data.verified) {
                this.verified = true;
                window.location.href = data.redirect || '{{ route('user.dashboard') }}';
            }
            this.checking = false;
        })
        .catch(() => {
            this.checking = false;
        });
    }
}">
    <div class="text-center space-y-6">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100">
            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </div>

        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Verify Your Email Address</h2>
            <p class="text-sm text-gray-600 mb-4">
                Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?
            </p>
            
            @if(isset($user) && $user->email)
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm font-medium text-blue-900 mb-1">Verification email sent to:</p>
                    <p class="text-sm text-blue-700 font-semibold">{{ $user->email }}</p>
                </div>
            @endif
        </div>

        @if(session('status'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm text-green-800 font-medium">{{ session('status') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-800 font-medium">{{ session('error') }}</p>
            </div>
        @endif

        @if($errors->has('email'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-800 font-medium">{{ $errors->first('email') }}</p>
            </div>
        @endif

        {{-- Development Helper: Show verification link directly in development mode --}}
        @if((config('app.env') === 'local' || config('app.debug')) && session('dev_verification_url'))
            <div class="p-4 bg-yellow-50 border-2 border-yellow-400 rounded-lg">
                <p class="text-sm font-semibold text-yellow-900 mb-2">🔧 Development Mode: Verification Link</p>
                <p class="text-xs text-yellow-800 mb-3">
                    Since you're in development mode, here's your verification link (emails are logged, not sent):
                </p>
                <div class="bg-white p-3 rounded border border-yellow-300 mb-3">
                    <a 
                        href="{{ session('dev_verification_url') }}" 
                        class="text-xs text-blue-600 hover:text-blue-800 break-all underline"
                        target="_blank"
                    >
                        {{ session('dev_verification_url') }}
                    </a>
                </div>
                <a 
                    href="{{ session('dev_verification_url') }}" 
                    class="block w-full px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors font-semibold text-sm text-center"
                >
                    Click Here to Verify Email
                </a>
            </div>
        @endif

        <div x-show="!verified" class="space-y-4">
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg text-left">
                <p class="text-xs font-semibold text-gray-700 mb-2">What to do next:</p>
                <ol class="text-xs text-gray-600 space-y-1.5 list-decimal list-inside">
                    <li>Check your email inbox (and spam folder)</li>
                    <li>Click the verification link in the email</li>
                    <li>You'll be automatically logged in and redirected</li>
                </ol>
            </div>

            <form method="POST" action="{{ route('verification.send') }}" x-data="{ processing: false }" @submit.prevent="processing = true; $el.submit();">
                @csrf
                <button 
                    type="submit" 
                    :disabled="processing"
                    class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                >
                    <span x-show="!processing">Resend Verification Email</span>
                    <span x-show="processing" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sending...
                    </span>
                </button>
            </form>

            <button 
                @click="checkVerification()"
                :disabled="checking"
                class="w-full px-6 py-2.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                x-init="setInterval(() => { if (!verified && !checking) checkVerification(); }, 5000)"
            >
                <span x-show="!checking">Check Verification Status</span>
                <span x-show="checking" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Checking...
                </span>
            </button>
        </div>

        <div x-show="verified" class="p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm text-green-800 font-medium">Email verified! Redirecting...</p>
        </div>

        <div class="pt-4 border-t border-gray-200">
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                    Back to Login
                </a>
            @endauth
        </div>
    </div>
</div>
@endsection
