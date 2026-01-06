@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="max-w-4xl space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Settings</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Manage your account and application settings</p>
    </div>

    <!-- Profile Settings -->
    <x-card>
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Profile Information</h2>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <x-input 
                type="text" 
                name="name" 
                label="Name" 
                value="{{ old('name', auth()->user()->name) }}"
                required
            />

            <x-input 
                type="email" 
                name="email" 
                label="Email Address" 
                value="{{ old('email', auth()->user()->email) }}"
                required
            />

            <div class="flex items-center justify-end">
                <x-button type="submit" variant="primary">Save Changes</x-button>
            </div>
        </form>
    </x-card>

    <!-- Platform Fee Settings -->
    @can('viewAny', App\Models\PlatformFee::class)
    <x-card>
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Platform Fee Configuration</h2>
        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">Configure how platform fees are calculated and applied to invoices.</p>
        
        @if(isset($platformFeeSettings))
            <div class="space-y-4">
                <div class="flex items-center justify-between py-3 border-b border-gray-200">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Fee Rate</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $platformFeeSettings['rate'] ?? 0 }}%</p>
                    </div>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-gray-200">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Total Fees Collected</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">${{ number_format($platformFeeSettings['total_collected'] ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>
        @else
            <p class="text-sm text-gray-600 dark:text-gray-300">No platform fee settings configured.</p>
        @endif
    </x-card>
    @endcan

    <!-- Account Actions -->
    <x-card>
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Account Actions</h2>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3 border-b border-gray-200">
                <div>
                    <p class="text-sm font-medium text-gray-900">Change Password</p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Update your account password</p>
                </div>
                <x-button variant="outline" size="sm">Change</x-button>
            </div>
            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-sm font-medium text-red-900">Delete Account</p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Permanently delete your account and all data</p>
                </div>
                <x-button variant="danger" size="sm">Delete</x-button>
            </div>
        </div>
    </x-card>
</div>
@endsection

