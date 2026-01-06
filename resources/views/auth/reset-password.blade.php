@extends('layouts.public')

@section('title', 'Reset Password')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Reset your password</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-gray-300">
                Enter your new password below to complete the reset process.
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-[#242424] rounded-xl shadow-lg dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)] p-8 border border-slate-200 dark:border-[#333333]">
            <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                @csrf

                <!-- Token -->
                <input type="hidden" name="token" value="{{ $token }}">

                <x-input 
                    type="email" 
                    name="email" 
                    label="Email address" 
                    value="{{ old('email', $email ?? '') }}"
                    required 
                    autofocus
                />

                <x-input 
                    type="password" 
                    name="password" 
                    label="New Password" 
                    required
                />

                <x-input 
                    type="password" 
                    name="password_confirmation" 
                    label="Confirm New Password" 
                    required
                />

                <div>
                    <x-button type="submit" variant="primary" class="w-full">
                        Reset Password
                    </x-button>
                </div>

                <div class="text-center text-sm">
                    <a href="{{ route('login') }}" class="font-medium text-[#00C4B4] dark:text-[#00C4B4] hover:text-[#00A896]">
                        ← Back to login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

