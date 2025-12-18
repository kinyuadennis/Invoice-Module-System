@extends('layouts.user')

@section('title', 'Create Invoice')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    @php
    $user = auth()->user();
    $userCompanies = $user->ownedCompanies()->get();
    $selectedCompanyId = request('company_id') ?? $company->id;
    @endphp

    @if($userCompanies->count() > 1)
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <label for="company-selector" class="block text-sm font-semibold text-slate-700 mb-1">
                    Select Company
                </label>
                <p class="text-xs text-slate-500">Choose which company to create this invoice for</p>
            </div>
            <div class="w-72">
                <select
                    id="company-selector"
                    x-data="{ companyId: '{{ $selectedCompanyId }}' }"
                    x-model="companyId"
                    @change="window.location.href = '{{ route('user.invoices.create') }}?company_id=' + companyId"
                    class="block w-full px-4 py-2.5 rounded-lg border border-slate-300 shadow-sm text-sm min-h-[44px] focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors">
                    @foreach($userCompanies as $comp)
                    <option value="{{ $comp->id }}" {{ $comp->id == $selectedCompanyId ? 'selected' : '' }}>
                        {{ $comp->name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    @endif

    <x-invoice-wizard :clients="$clients" :services="$services" :company="$company" :nextInvoiceNumber="$nextInvoiceNumber ?? null" />
</div>
@endsection