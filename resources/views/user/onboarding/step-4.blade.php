@extends('user.onboarding.layout')

@section('title', 'Logo & Branding')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-8 md:p-12">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Logo & Branding</h1>
        <p class="text-gray-600">Upload your company logo to personalize your invoices</p>
    </div>

    <form method="POST" action="{{ route('user.onboarding.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <input type="hidden" name="step" value="4">

        <div>
            <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                Company Logo
            </label>
            <div class="mt-1 flex items-center space-x-6">
                @if(isset($company) && $company->logo)
                    <img src="{{ Storage::url($company->logo) }}" alt="Company Logo" class="h-20 w-20 object-contain border border-gray-200 rounded-lg p-2">
                @else
                    <div class="h-20 w-20 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
                <div class="flex-1">
                    <input
                        type="file"
                        id="logo"
                        name="logo"
                        accept="image/jpeg,image/png,image/jpg,image/gif,image/svg"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#2B6EF6] file:text-white hover:file:bg-[#2563EB]"
                    >
                    <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF or SVG (Max 2MB)</p>
                </div>
            </div>
            @error('logo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-sm text-blue-800">
                <strong>Tip:</strong> Your logo will appear on all invoices. For best results, use a transparent PNG with a height of 100-200px.
            </p>
        </div>

        <div class="flex gap-4 pt-4">
            <button type="submit" name="action" value="back" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                Back
            </button>
            <button type="submit" name="action" value="skip" class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                Skip
            </button>
            <button type="submit" name="action" value="next" class="flex-1 px-6 py-3 bg-[#2B6EF6] text-white font-semibold rounded-lg hover:bg-[#2563EB] transition-colors">
                Continue
            </button>
        </div>
    </form>
</div>
@endsection

