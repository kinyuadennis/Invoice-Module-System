@extends('layouts.guest')

@section('title', 'Company Setup')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Set Up Your Company
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-300">
                Create your company workspace to get started
            </p>
        </div>

        @if(session('error'))
            <x-alert type="error">{{ session('error') }}</x-alert>
        @endif

        <form class="mt-8 space-y-6" method="POST" action="{{ route('company.store') }}" enctype="multipart/form-data">
            @csrf

            <x-card>
                <div class="space-y-6">
                    <div>
                        <x-input
                            type="text"
                            name="name"
                            label="Company Name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            placeholder="Enter your company name"
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
                            value="{{ old('email') }}"
                            placeholder="company@example.com"
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
                            value="{{ old('phone') }}"
                            placeholder="+254 700 000 000"
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
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Enter company address"
                        >{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-input
                            type="text"
                            name="kra_pin"
                            label="KRA PIN (Optional)"
                            value="{{ old('kra_pin') }}"
                            placeholder="P051234567A"
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
                            value="{{ old('invoice_prefix', 'INV') }}"
                            placeholder="INV"
                            maxlength="10"
                        />
                        <p class="mt-1 text-sm text-gray-500">
                            This prefix will be used for invoice numbers (e.g., INV-0001)
                        </p>
                        @error('invoice_prefix')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="logo" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                            Company Logo (Optional)
                        </label>
                        <input
                            type="file"
                            id="logo"
                            name="logo"
                            accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                        @error('logo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-card>

            <div>
                <x-button type="submit" variant="primary" class="w-full">
                    Create Company & Continue
                </x-button>
            </div>
        </form>
    </div>
</div>
@endsection

