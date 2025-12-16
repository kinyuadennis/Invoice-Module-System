@extends('user.onboarding.layout')

@section('title', 'Company Basics')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-8 md:p-12">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Company Basics</h1>
        <p class="text-gray-600">Let's start with your company's basic information</p>
    </div>

    <form method="POST" action="{{ route('user.onboarding.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="step" value="2">

        <div>
            <x-input
                type="text"
                name="name"
                label="Company Name"
                value="{{ old('name', $company->name ?? '') }}"
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
                value="{{ old('email', $company->email ?? '') }}"
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
                value="{{ old('phone', $company->phone ?? '') }}"
                placeholder="+254 700 000 000"
            />
            @error('phone')
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

