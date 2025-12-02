@extends('layouts.public')

@section('title', 'Register')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <h2 class="text-3xl font-bold text-slate-900">Create your account</h2>
            <p class="mt-2 text-sm text-slate-600">
                Already have an account?
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    Sign in
                </a>
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-lg p-8 border border-slate-200">
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

                <div class="flex items-start">
                    <input id="terms" name="terms" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded mt-1" required>
                    <label for="terms" class="ml-2 block text-sm text-slate-700">
                        I agree to the <a href="#" class="text-blue-600 hover:text-blue-500">Terms of Service</a> and <a href="#" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>
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
                    <span class="text-slate-600">Already have an account?</span>
                    <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500 ml-1">Sign in</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

