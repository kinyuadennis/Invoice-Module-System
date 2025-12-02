@extends('layouts.public')

@section('title', 'Verify Email')
@section('heading', 'Verify Your Email Address')

@section('content')
<div class="max-w-md mx-auto">
    <div class="text-center space-y-4">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </div>

        <p class="text-sm text-slate-600">
            Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
        </p>

        @if(session('status'))
            <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm text-green-800">{{ session('status') }}</p>
            </div>
        @endif

        @if($errors->has('email'))
            <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-800">{{ $errors->first('email') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" x-data="{ processing: false, nextAllowedAt: null }" @submit.prevent="processing = true; $el.submit();">
            @csrf
            <button 
                type="submit" 
                :disabled="processing"
                class="w-full mt-6 px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
                <span x-show="!processing">Resend Verification Email</span>
                <span x-show="processing" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Sending...
                </span>
            </button>
        </form>

        @auth
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="text-sm text-slate-600 hover:text-slate-900">
                    Logout
                </button>
            </form>
        @else
            <div class="mt-4 text-center">
                <a href="{{ route('login') }}" class="text-sm text-slate-600 hover:text-slate-900">
                    Back to Login
                </a>
            </div>
        @endauth
    </div>
</div>
@endsection
