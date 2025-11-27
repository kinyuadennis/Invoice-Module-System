@extends('layouts.public')

@section('title', 'Login')
@section('heading', 'Sign in to your account')

@section('content')
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
            <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <label for="remember" class="ml-2 block text-sm text-gray-900">Remember me</label>
        </div>

        <div class="text-sm">
            <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">Forgot your password?</a>
        </div>
    </div>

    <div>
        <x-button type="submit" variant="primary" class="w-full">
            Sign in
        </x-button>
    </div>

    <div class="text-center text-sm">
        <span class="text-gray-600">Don't have an account?</span>
        <a href="{{ route('register.form') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Sign up</a>
    </div>
</form>
@endsection

