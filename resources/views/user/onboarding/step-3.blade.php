@extends('user.onboarding.layout')

@section('title', 'Business Details')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-8 md:p-12">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Business Details</h1>
        <p class="text-gray-600">Add your business address and tax information</p>
    </div>

    <form method="POST" action="{{ route('user.onboarding.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="step" value="3">

        <div>
            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                Company Address
            </label>
            <textarea
                id="address"
                name="address"
                rows="3"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#2B6EF6] focus:ring-[#2B6EF6]"
                placeholder="Enter company address"
            >{{ old('address', $company->address ?? '') }}</textarea>
            @error('address')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-input
                type="text"
                name="kra_pin"
                label="KRA PIN (Optional)"
                value="{{ old('kra_pin', $company->kra_pin ?? '') }}"
                placeholder="P051234567A"
            />
            <p class="mt-1 text-xs text-gray-500">Required for eTIMS compliance in Kenya</p>
            @error('kra_pin')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">
                Currency
            </label>
            <select
                id="currency"
                name="currency"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#2B6EF6] focus:ring-[#2B6EF6]"
            >
                <option value="KES" {{ old('currency', $company->currency ?? 'KES') === 'KES' ? 'selected' : '' }}>KES - Kenyan Shilling</option>
                <option value="USD" {{ old('currency', $company->currency ?? '') === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                <option value="EUR" {{ old('currency', $company->currency ?? '') === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                <option value="GBP" {{ old('currency', $company->currency ?? '') === 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
            </select>
            @error('currency')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">
                Timezone
            </label>
            <select
                id="timezone"
                name="timezone"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#2B6EF6] focus:ring-[#2B6EF6]"
            >
                <option value="Africa/Nairobi" {{ old('timezone', $company->timezone ?? 'Africa/Nairobi') === 'Africa/Nairobi' ? 'selected' : '' }}>Africa/Nairobi (EAT)</option>
                <option value="UTC" {{ old('timezone', $company->timezone ?? '') === 'UTC' ? 'selected' : '' }}>UTC</option>
            </select>
            @error('timezone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-4 pt-4">
            <button type="submit" name="action" value="back" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                Back
            </button>
            <button type="submit" name="action" value="next" class="flex-1 px-6 py-3 bg-[#2B6EF6] text-white font-semibold rounded-lg hover:bg-[#2563EB] transition-colors">
                Continue
            </button>
        </div>
    </form>
</div>
@endsection

