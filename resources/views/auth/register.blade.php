@extends('layouts.public')

@section('title', 'Register')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Create your account</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-gray-300">
                Already have an account?
                <a href="{{ route('login') }}" class="font-medium text-[#00C4B4] dark:text-[#00C4B4] hover:text-[#00A896]">
                    Sign in
                </a>
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-[#242424] rounded-xl shadow-lg dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)] p-8 border border-slate-200 dark:border-[#333333]">
            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <x-input 
                    type="text" 
                    name="name" 
                    label="Full Name" 
                    value="{{ old('name') }}"
                    required 
                    autofocus
                />

                <x-input 
                    type="email" 
                    name="email" 
                    label="Email address" 
                    value="{{ old('email') }}"
                    required
                />

                <x-input 
                    type="password" 
                    name="password" 
                    label="Password" 
                    required
                />

                <x-input 
                    type="password" 
                    name="password_confirmation" 
                    label="Confirm Password" 
                    required
                />

                <div class="border-t border-slate-200 dark:border-[#333333] pt-6">
                    <p class="text-sm font-medium text-slate-700 dark:text-gray-200 mb-4">Optional: Quick Company Setup</p>
                    <p class="text-xs text-slate-500 mb-4">You can set up your company now or complete it later during onboarding.</p>
                    <x-input 
                        type="text" 
                        name="company_name" 
                        label="Company Name (Optional)" 
                        value="{{ old('company_name') }}"
                        placeholder="Enter your company name"
                    />
                    <p class="mt-1 text-xs text-slate-500">This will create your first company automatically</p>
                </div>

                <div class="flex items-start">
                    <input id="terms" name="terms" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded mt-1" required>
                    <label for="terms" class="ml-2 block text-sm text-slate-700 dark:text-gray-200">
                        I agree to the <a href="#" class="text-[#00C4B4] dark:text-[#00C4B4] hover:text-[#00A896]">Terms of Service</a> and <a href="#" class="text-[#00C4B4] dark:text-[#00C4B4] hover:text-[#00A896]">Privacy Policy</a>
                    </label>
                </div>
                @error('terms')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div>
                    <x-button type="submit" variant="primary" class="w-full">
                        Create Account
                    </x-button>
                </div>

                <div class="text-center text-sm">
                    <span class="text-slate-600 dark:text-gray-300">Already have an account?</span>
                    <a href="{{ route('login') }}" class="font-medium text-[#00C4B4] dark:text-[#00C4B4] hover:text-[#00A896] ml-1">Sign in</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

