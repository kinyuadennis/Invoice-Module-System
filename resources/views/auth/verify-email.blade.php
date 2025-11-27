@extends('layouts.public')

@section('title', 'Verify Email')
@section('heading', 'Verify Your Email Address')

@section('content')
<div class="text-center space-y-4">
    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100">
        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
    </div>

    <p class="text-sm text-gray-600">
        Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
    </p>

    @if(session('status') === 'verification-link-sent')
        <x-alert type="success" class="mt-4">
            A new verification link has been sent to the email address you provided during registration.
        </x-alert>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
        @csrf
        <x-button type="submit" variant="primary" class="w-full">
            Resend Verification Email
        </x-button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">
            Logout
        </button>
    </form>
</div>
@endsection

