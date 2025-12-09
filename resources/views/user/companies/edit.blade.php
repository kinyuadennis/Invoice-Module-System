@extends('layouts.user')

@section('title', 'Edit Company')

@section('content')
<div class="space-y-6 mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Edit Company</h1>
        <p class="mt-1 text-sm text-gray-600">Update company information</p>
    </div>

    @if($errors->any())
        <x-alert type="error">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <form method="POST" action="{{ route('user.companies.update', $company->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <x-card>
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Company Information</h2>

                    <div class="space-y-4">
                        <div>
                            <x-input
                                type="text"
                                name="name"
                                label="Company Name"
                                value="{{ old('name', $company->name) }}"
                                required
                            />
                        </div>

                        <div>
                            <x-input
                                type="email"
                                name="email"
                                label="Company Email"
                                value="{{ old('email', $company->email) }}"
                            />
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
                            <label for="address" class="block text-[var(--font-size-sm)] font-semibold text-[var(--color-neutral-700)] mb-1">
                                Company Address
                            </label>
                            <textarea
                                id="address"
                                name="address"
                                rows="3"
                                class="block w-full px-[var(--spacing-4)] py-[var(--spacing-3)] rounded-[var(--border-radius-md)] border border-[var(--color-neutral-200)] shadow-sm text-[var(--font-size-base)] focus:border-[var(--color-primary-500)] focus:ring-0 focus:shadow-[0_0_0_4px_rgba(var(--color-primary-rgb),0.08)]"
                            >{{ old('address', $company->address) }}</textarea>
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

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-select
                                    name="currency"
                                    label="Currency"
                                    :options="[
                                        ['value' => 'KES', 'label' => 'KES - Kenyan Shilling'],
                                        ['value' => 'USD', 'label' => 'USD - US Dollar'],
                                        ['value' => 'EUR', 'label' => 'EUR - Euro'],
                                        ['value' => 'GBP', 'label' => 'GBP - British Pound'],
                                    ]"
                                    value="{{ old('currency', $company->currency ?? 'KES') }}"
                                    required
                                />
                            </div>

                            <div>
                                <x-select
                                    name="timezone"
                                    label="Timezone"
                                    :options="[
                                        ['value' => 'Africa/Nairobi', 'label' => 'Africa/Nairobi (EAT)'],
                                        ['value' => 'UTC', 'label' => 'UTC'],
                                        ['value' => 'America/New_York', 'label' => 'America/New_York (EST)'],
                                        ['value' => 'Europe/London', 'label' => 'Europe/London (GMT)'],
                                    ]"
                                    value="{{ old('timezone', $company->timezone ?? 'Africa/Nairobi') }}"
                                    required
                                />
                            </div>
                        </div>

                        <div>
                            <label for="logo" class="block text-[var(--font-size-sm)] font-semibold text-[var(--color-neutral-700)] mb-1">
                                Company Logo
                            </label>
                            @if($company->logo)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($company->logo) }}" alt="{{ $company->name }}" class="h-16 w-16 rounded-lg object-contain border border-gray-200">
                                </div>
                            @endif
                            <input
                                type="file"
                                id="logo"
                                name="logo"
                                accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#2B6EF6] file:text-white hover:file:bg-[#2563EB]"
                            >
                            <p class="mt-1 text-xs text-gray-500">Recommended: Square image, max 2MB</p>
                        </div>

                        <div>
                            <x-select
                                name="default_invoice_template_id"
                                label="Default Invoice Template"
                                :options="$templates->map(fn($t) => ['value' => $t->id, 'label' => $t->name])->toArray()"
                                value="{{ old('default_invoice_template_id', $company->default_invoice_template_id ?? $company->invoice_template_id) }}"
                            />
                        </div>
                    </div>
                </x-card>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3 mt-6">
            <a href="{{ route('user.companies.index') }}">
                <x-button type="button" variant="secondary">Cancel</x-button>
            </a>
            <x-button type="submit" variant="primary">Update Company</x-button>
        </div>
    </form>
</div>
@endsection
