@extends('layouts.user')

@section('title', 'Company Settings')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Company Settings</h1>
        <p class="mt-1 text-sm text-gray-600">Manage your company information and preferences</p>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif

    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <form method="POST" action="{{ route('user.company.update') }}" enctype="multipart/form-data">
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
                            <p class="mt-1 text-xs text-gray-500">Format: +254712345678 or 0712345678</p>
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                                Company Address
                            </label>
                            <textarea
                                id="address"
                                name="address"
                                rows="3"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                            <p class="mt-1 text-xs text-gray-500">Format: Letter + 9 digits + Letter (e.g., A012345678B)</p>
                        </div>

                        <div>
                            @php
                                $activePrefix = $company->activeInvoicePrefix();
                            @endphp
                            <x-input
                                type="text"
                                name="invoice_prefix"
                                label="Invoice Prefix"
                                value="{{ old('invoice_prefix', $activePrefix?->prefix ?? $company->invoice_prefix ?? 'INV') }}"
                                maxlength="50"
                            />
                            <p class="mt-1 text-sm text-gray-500">
                                @if($activePrefix)
                                    Current active prefix: <strong>{{ $activePrefix->prefix }}</strong>
                                    <br>
                                    <span class="text-xs text-gray-400">Active since {{ $activePrefix->started_at->format('M d, Y') }}</span>
                                @else
                                    No active prefix set. Default will be used.
                                @endif
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                You can use placeholders: %YYYY% (year), %MM% (month), %DD% (day), %YY% (2-digit year), %MMMM% (month name)
                            </p>
                            <p class="mt-2 text-xs text-amber-600">
                                ⚠️ Changing the prefix will only affect new invoices. Existing invoices will keep their original prefix.
                            </p>
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
                                class="w-32 h-32 object-contain rounded-lg border border-gray-200"
                            />
                        </div>
                    @endif

                    <div>
                        <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
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

                <!-- Invoice Customization Link - Removed, now accessible from Invoice section -->

                <!-- Actions -->
                <div>
                    <x-button type="submit" variant="primary" class="w-full">
                        Save Changes
                    </x-button>
                </div>
            </div>
        </div>
    </form>

    <!-- Payment Methods Section -->
    <x-payment-methods-section :company="$company" :paymentMethods="$paymentMethods" />
</div>
@endsection

