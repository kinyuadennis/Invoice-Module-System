@extends('layouts.user')

@section('title', 'Manage Companies')

@section('content')
<div class="space-y-6 mb-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manage Companies</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Create and manage your companies</p>
        </div>
        <a href="{{ route('user.companies.create') }}">
            <x-button variant="primary">
                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Company
            </x-button>
        </a>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif

    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <!-- Companies Grid -->
    @if($companies->count() > 0)
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($companies as $company)
                <x-card>
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            @if($company->logo)
                                <img src="{{ Storage::url($company->logo) }}" alt="{{ $company->name }}" class="h-12 w-12 rounded-lg object-contain">
                            @else
                                <div class="h-12 w-12 rounded-lg bg-[#2B6EF6] flex items-center justify-center text-white font-semibold text-lg">
                                    {{ strtoupper(substr($company->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $company->name }}</h3>
                                @if($company->id === $activeCompany?->id)
                                    <x-badge variant="primary" class="mt-1">Active</x-badge>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300 mb-4">
                        @if($company->email)
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {{ $company->email }}
                            </div>
                        @endif
                        @if($company->phone)
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                {{ $company->phone }}
                            </div>
                        @endif
                        <div class="flex items-center">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $company->currency ?? 'KES' }}
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 pt-4 border-t border-gray-200">
                        @if($company->id !== $activeCompany?->id)
                            <form method="POST" action="{{ route('user.company.switch') }}" class="flex-1">
                                @csrf
                                <input type="hidden" name="company_id" value="{{ $company->id }}">
                                <x-button type="submit" variant="outline" size="sm" class="w-full">
                                    Switch to This Company
                                </x-button>
                            </form>
                        @endif
                        <a href="{{ route('user.companies.edit', $company->id) }}" class="flex-1">
                            <x-button variant="secondary" size="sm" class="w-full">
                                Edit
                            </x-button>
                        </a>
                        @if($companies->count() > 1)
                            <form method="POST" action="{{ route('user.companies.destroy', $company->id) }}" onsubmit="return confirm('Are you sure you want to delete this company? This action cannot be undone.');" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="danger" size="sm" class="w-full">
                                    Delete
                                </x-button>
                            </form>
                        @endif
                    </div>
                </x-card>
            @endforeach
        </div>
    @else
        <x-card>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">No companies</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new company.</p>
                <div class="mt-6">
                    <a href="{{ route('user.companies.create') }}">
                        <x-button variant="primary">
                            <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            New Company
                        </x-button>
                    </a>
                </div>
            </div>
        </x-card>
    @endif
</div>
@endsection
