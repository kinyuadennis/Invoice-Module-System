@extends('layouts.admin')

@section('title', 'Edit Company')

@section('content')
<div class="max-w-4xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Edit Company</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Update company information</p>
    </div>

    <form method="POST" action="{{ route('admin.companies.update', $company->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <x-card>
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Company Information</h2>

                    <div class="space-y-6">
                        <div>
                            <x-input
                                type="text"
                                name="name"
                                label="Company Name"
                                value="{{ old('name', $company->name) }}"
                                required
                            />
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input
                                type="email"
                                name="email"
                                label="Company Email"
                                value="{{ old('email', $company->email) }}"
                            />
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="phone"
                                label="Company Phone"
                                value="{{ old('phone', $company->phone) }}"
                            />
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                                Company Address
                            </label>
                            <textarea
                                id="address"
                                name="address"
                                rows="3"
                                class="w-full rounded-lg border-gray-300 dark:border-[#404040] shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >{{ old('address', $company->address) }}</textarea>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="kra_pin"
                                label="KRA PIN"
                                value="{{ old('kra_pin', $company->kra_pin) }}"
                            />
                            @error('kra_pin')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="invoice_prefix"
                                label="Invoice Prefix"
                                value="{{ old('invoice_prefix', $company->invoice_prefix) }}"
                                maxlength="10"
                            />
                            <p class="mt-1 text-sm text-gray-500">
                                Current format: {{ $company->invoice_prefix }}-0001
                            </p>
                            @error('invoice_prefix')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Logo Upload -->
                <x-card>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Company Logo</h2>

                    @if($company->logo)
                        <div class="mb-4">
                            <img
                                src="{{ Storage::url($company->logo) }}"
                                alt="{{ $company->name }} Logo"
                                class="w-32 h-32 object-contain rounded-lg border border-gray-200 dark:border-[#333333]"
                            />
                        </div>
                    @endif

                    <div>
                        <label for="logo" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Upload New Logo
                        </label>
                        <input
                            type="file"
                            id="logo"
                            name="logo"
                            accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                        <p class="mt-1 text-xs text-gray-500">Max 2MB. Recommended: 200x200px</p>
                        @error('logo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </x-card>

                <!-- Actions -->
                <div>
                    <x-button type="submit" variant="primary" class="w-full">
                        Save Changes
                    </x-button>
                    <a href="{{ route('admin.companies.show', $company->id) }}" class="block mt-3">
                        <x-button type="button" variant="outline" class="w-full">
                            Cancel
                        </x-button>
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

