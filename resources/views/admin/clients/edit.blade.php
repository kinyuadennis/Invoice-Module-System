@extends('layouts.admin')

@section('title', 'Edit Client')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Edit Client</h1>
    </div>

    <x-card>
        <form method="POST" action="{{ route('admin.clients.update', $client['id'] ?? 0) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <x-select name="company_id" label="Company" :options="array_merge([['value' => '', 'label' => 'Select Company']], $companies->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray())" value="{{ old('company_id', $client['company_id'] ?? '') }}" required />
            <x-input type="text" name="name" label="Client Name" value="{{ old('name', $client['name'] ?? '') }}" required autofocus />
            <x-input type="email" name="email" label="Email Address" value="{{ old('email', $client['email'] ?? '') }}" />
            <x-input type="tel" name="phone" label="Phone Number" value="{{ old('phone', $client['phone'] ?? '') }}" />
            <x-input type="text" name="kra_pin" label="KRA PIN" value="{{ old('kra_pin', $client['kra_pin'] ?? '') }}" />

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Address</label>
                <textarea name="address" id="address" rows="3" class="block w-full rounded-md border-gray-300 dark:border-[#404040] shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('address', $client['address'] ?? '') }}</textarea>
            </div>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('admin.clients.index') }}">
                    <x-button type="button" variant="outline">Cancel</x-button>
                </a>
                <x-button type="submit" variant="primary">Update Client</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection

