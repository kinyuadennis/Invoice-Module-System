@extends('user.onboarding.layout')

@section('title', 'Invoice Preferences')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-8 md:p-12">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Invoice Preferences</h1>
        <p class="text-gray-600 dark:text-gray-300">Configure your default invoice settings</p>
    </div>

    <form method="POST" action="{{ route('user.onboarding.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="step" value="5">

        <div>
            <x-input
                type="text"
                name="invoice_prefix"
                label="Invoice Prefix"
                value="{{ old('invoice_prefix', $company->invoice_prefix ?? 'INV') }}"
                placeholder="INV"
                maxlength="10"
            />
            <p class="mt-1 text-xs text-gray-500">This prefix will be used for all your invoices (e.g., INV-001, INV-002)</p>
            @error('invoice_prefix')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="default_invoice_template_id" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                Default Invoice Template
            </label>
            <select
                id="default_invoice_template_id"
                name="default_invoice_template_id"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#2B6EF6] focus:ring-[#2B6EF6]"
            >
                <option value="">Select a template</option>
                @foreach($templates as $template)
                    <option value="{{ $template->id }}" {{ old('default_invoice_template_id', $company->default_invoice_template_id ?? '') == $template->id ? 'selected' : '' }}>
                        {{ $template->name }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">You can change this later in company settings</p>
            @error('default_invoice_template_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if($templates->count() > 0)
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Available Templates:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($templates->take(4) as $template)
                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            • {{ $template->name }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex gap-4 pt-4">
            <button type="submit" name="action" value="back" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 dark:text-gray-200 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                Back
            </button>
            <button type="submit" name="action" value="next" class="flex-1 px-6 py-3 bg-[#2B6EF6] text-white font-semibold rounded-lg hover:bg-[#2563EB] transition-colors">
                Continue
            </button>
        </div>
    </form>
</div>
@endsection

