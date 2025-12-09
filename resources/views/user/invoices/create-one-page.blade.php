@extends('layouts.user')

@section('title', 'Create Invoice')

@section('content')
<div class="max-w-7xl mx-auto space-y-4">
    <!-- Header with Customize Link -->
    <div class="flex items-center justify-between bg-white rounded-lg shadow-sm p-4 border border-gray-200">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create New Invoice</h1>
            <p class="text-sm text-gray-600 mt-1">Fill in the details below to create your invoice</p>
        </div>
        <a href="{{ route('user.company.invoice-customization') }}" class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
            </svg>
            <span>Customize Invoice Settings</span>
        </a>
    </div>

    @php
        $user = auth()->user();
        $userCompanies = $user->ownedCompanies()->get();
        $selectedCompanyId = $selectedCompanyId ?? $company->id;
    @endphp

    @if($userCompanies->count() > 1)
        <div class="mb-6">
            <x-card>
                <div class="flex items-center justify-between">
                    <div>
                        <label for="company-selector" class="block text-sm font-semibold text-gray-700 mb-2">
                            Select Company
                        </label>
                        <p class="text-xs text-gray-500">Choose which company to create this invoice for</p>
                    </div>
                    <div class="w-64">
                        <select 
                            id="company-selector"
                            x-data="{ companyId: '{{ $selectedCompanyId }}' }"
                            x-model="companyId"
                            @change="window.location.href = '{{ route('user.invoices.create') }}?company_id=' + companyId"
                            class="block w-full px-[var(--spacing-4)] py-[var(--spacing-3)] rounded-[var(--border-radius-md)] border border-[var(--color-neutral-200)] shadow-sm text-[var(--font-size-base)] min-h-[44px] focus:border-[var(--color-primary-500)] focus:ring-0 focus:shadow-[0_0_0_4px_rgba(var(--color-primary-rgb),0.08)]"
                        >
                            @foreach($userCompanies as $comp)
                                <option value="{{ $comp->id }}" {{ $comp->id == $selectedCompanyId ? 'selected' : '' }}>
                                    {{ $comp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </x-card>
        </div>
    @endif

    <x-one-page-invoice-builder 
        :clients="$clients" 
        :services="$services" 
        :company="$company"
        :nextInvoiceNumber="$nextInvoiceNumber"
    />
</div>
@endsection

