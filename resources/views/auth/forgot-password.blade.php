@extends('layouts.public')

@section('title', 'Forgot Password')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <h2 class="text-3xl font-bold text-slate-900">Forgot your password?</h2>
            <p class="mt-2 text-sm text-slate-600">
                No worries! Enter your email address and we'll send you a link to reset your password.
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-lg p-8 border border-slate-200">
            @if (session('status'))
                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">{{ session('status') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                <x-input 
                    type="email" 
                    name="email" 
                    label="Email address" 
                    value="{{ old('email') }}"
                    required 
                    autofocus
                />

                <div>
                    <x-button type="submit" variant="primary" class="w-full">
                        Send Password Reset Link
                    </x-button>
                </div>

                <div class="text-center text-sm">
                    <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                        ← Back to login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

