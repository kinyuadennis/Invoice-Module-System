@extends('layouts.public')

@section('title', 'Register')
@section('heading', 'Create your account')

@section('content')
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

    <div class="flex items-center">
        <input id="terms" name="terms" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" required>
        <label for="terms" class="ml-2 block text-sm text-gray-900">
            I agree to the <a href="#" class="text-indigo-600 hover:text-indigo-500">Terms of Service</a>
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
        <span class="text-gray-600">Already have an account?</span>
        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Sign in</a>
    </div>
</form>
@endsection

