@extends('layouts.user')

@section('title', 'Profile')

@section('content')
<div class="max-w-4xl" x-data="{ activeTab: 'profile' }">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Your Profile</h1>
        <p class="mt-1 text-sm text-gray-600">Manage your account settings and preferences</p>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button
                @click="activeTab = 'profile'"
                :class="activeTab === 'profile' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
            >
                Profile
            </button>
            <button
                @click="activeTab = 'settings'"
                :class="activeTab === 'settings' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
            >
                Settings
            </button>
            @if($company && (auth()->user()->role === 'company_owner' || auth()->user()->role === 'admin'))
                <button
                    @click="activeTab = 'company'"
                    :class="activeTab === 'company' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                >
                    Company Details
                </button>
            @endif
        </nav>
    </div>

    <!-- Profile Tab -->
    <div x-show="activeTab === 'profile'" x-cloak>
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Profile Information</h2>
            
            <form method="POST" action="{{ route('user.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Profile Photo -->
                <div class="flex items-center gap-6">
                    <div class="flex-shrink-0">
                        @if($user->profile_photo_path)
                            <img 
                                src="{{ Storage::url($user->profile_photo_path) }}" 
                                alt="{{ $user->name }}"
                                class="w-24 h-24 rounded-full object-cover border-4 border-gray-200"
                            >
                        @else
                            <div class="w-24 h-24 rounded-full bg-blue-500 flex items-center justify-center text-white text-3xl font-bold border-4 border-gray-200">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Profile Photo</label>
                        <input
                            type="file"
                            name="profile_photo"
                            accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                        >
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG or GIF. Max size 2MB.</p>
                        @if($user->profile_photo_path)
                            <form method="POST" action="{{ route('user.profile.photo.delete') }}" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:text-red-700">Remove photo</button>
                            </form>
                        @endif
                    </div>
                </div>

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
                    <p class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $user->role ?? 'user')) }}</p>
                </div>

                <div class="flex items-center justify-end pt-4">
                    <x-button type="submit" variant="primary">Save Changes</x-button>
                </div>
            </form>
        </x-card>

        <!-- Quick Links -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('user.invoices.index') }}" class="block p-4 bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div>
                        <p class="font-medium text-gray-900">My Invoices</p>
                        <p class="text-sm text-gray-500">View all invoices</p>
                    </div>
                </div>
            </a>
            
            <a href="{{ route('user.payments.index') }}" class="block p-4 bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-medium text-gray-900">Payment Methods</p>
                        <p class="text-sm text-gray-500">Manage payments</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Settings Tab -->
    <div x-show="activeTab === 'settings'" x-cloak>
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Password</h2>
            
            <form method="POST" action="{{ route('user.profile.password.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <x-input 
                    type="password" 
                    name="current_password" 
                    label="Current Password" 
                    required
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

                <div class="flex items-center justify-end pt-4">
                    <x-button type="submit" variant="primary">Update Password</x-button>
                </div>
            </form>
        </x-card>

        <x-card class="mt-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Notifications</h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Email Notifications</p>
                        <p class="text-sm text-gray-500">Receive email updates about your account</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Invoice Reminders</p>
                        <p class="text-sm text-gray-500">Get reminders for overdue invoices</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Company Details Tab -->
    @if($company && (auth()->user()->role === 'company_owner' || auth()->user()->role === 'admin'))
        <div x-show="activeTab === 'company'" x-cloak>
            <x-card>
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Company Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                        <p class="text-sm text-gray-900">{{ $company->name }}</p>
                    </div>

                    @if($company->kra_pin)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">KRA PIN</label>
                            <p class="text-sm text-gray-900">{{ $company->kra_pin }}</p>
                        </div>
                    @endif

                    @if($company->address)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <p class="text-sm text-gray-900">{{ $company->address }}</p>
                        </div>
                    @endif

                    @if($company->phone)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <p class="text-sm text-gray-900">{{ $company->phone }}</p>
                        </div>
                    @endif

                    <div class="pt-4">
                        <a href="{{ route('user.companies.edit', $company->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            Edit Company Details
                        </a>
                    </div>
                </div>
            </x-card>
        </div>
    @endif
</div>
@endsection
