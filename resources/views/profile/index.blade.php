@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Your Profile</h1>
        <p class="mt-1 text-sm text-gray-600">Manage your account information</p>
    </div>

    <x-card>
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Profile Information</h2>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <x-input 
                type="text" 
                name="name" 
                label="Name" 
                value="{{ old('name', $user->name) }}"
                required
            />

            <x-input 
                type="email" 
                name="email" 
                label="Email Address" 
                value="{{ old('email', $user->email) }}"
                required
            />

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <p class="text-sm text-gray-600">{{ ucfirst($user->role ?? 'user') }}</p>
            </div>

            <div class="flex items-center justify-end">
                <x-button type="submit" variant="primary">Save Changes</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection

