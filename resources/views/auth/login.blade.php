@extends('layouts.public')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <h2 class="text-3xl font-bold text-slate-900">Sign in to your account</h2>
            <p class="mt-2 text-sm text-slate-600">
                Or
                <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    create a new account
                </a>
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-lg p-8 border border-slate-200">
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <x-input 
                    type="email" 
                    name="email" 
                    label="Email address" 
                    value="{{ old('email') }}"
                    required 
                    autofocus
                />

                <x-input 
                    type="password" 
                    name="password" 
                    label="Password" 
                    required
                />

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-slate-700">Remember me</label>
                    </div>

                    <div class="text-sm">
                        <a href="{{ route('password.request') }}" class="font-medium text-blue-600 hover:text-blue-500">Forgot your password?</a>
                    </div>
                </div>

                <div>
                    <x-button type="submit" variant="primary" class="w-full">
                        Sign in
                    </x-button>
                </div>

                <div class="text-center text-sm">
                    <span class="text-slate-600">Don't have an account?</span>
                    <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-500 ml-1">Sign up</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

