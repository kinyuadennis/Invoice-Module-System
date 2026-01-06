@extends('layouts.app')

@section('title', 'Add Client')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Add Client</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Create a new client in your database</p>
    </div>

    <x-card>
        <form method="POST" action="{{ route('clients.store') }}" class="space-y-6">
            @csrf

            <x-input 
                type="text" 
                name="name" 
                label="Client Name" 
                value="{{ old('name') }}"
                required 
                autofocus
            />

            <x-input 
                type="email" 
                name="email" 
                label="Email Address" 
                value="{{ old('email') }}"
                required
            />

            <x-input 
                type="tel" 
                name="phone" 
                label="Phone Number" 
                value="{{ old('phone') }}"
            />

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Address</label>
                <textarea 
                    name="address" 
                    id="address"
                    rows="3"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="Street address, City, State, ZIP"
                >{{ old('address') }}</textarea>
                @error('address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('clients.index') }}">
                    <x-button type="button" variant="outline">Cancel</x-button>
                </a>
                <x-button type="submit" variant="primary">Create Client</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection

