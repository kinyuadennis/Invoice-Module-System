@extends('layouts.user')

@section('title', 'Manage Companies')

@section('content')
<div class="space-y-6 mb-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Manage Companies</h1>
            <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Create and manage your companies</p>
        </div>
        <a href="{{ route('user.companies.create') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 uppercase tracking-wider">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Company
        </a>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800 dark:text-green-400">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800 dark:text-red-400">{{ session('error') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Companies Grid -->
    @if($companies->count() > 0)
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach($companies as $company)
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm hover:border-blue-500/30 transition-all duration-300 group flex flex-col h-full">
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-center space-x-4">
                    @if($company->logo)
                    <img src="{{ Storage::url($company->logo) }}" alt="{{ $company->name }}" class="h-14 w-14 rounded-xl object-contain border border-gray-100 dark:border-[#2A2A2A] bg-gray-50 dark:bg-white/5">
                    @else
                    <div class="h-14 w-14 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-blue-500/20">
                        {{ strtoupper(substr($company->name, 0, 1)) }}
                    </div>
                    @endif
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">{{ $company->name }}</h3>
                        @if($company->id === $activeCompany?->id)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-400 mt-1">
                            Active
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-3 flex-grow">
                @if($company->email)
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200 transition-colors">
                    <div class="w-8 flex justify-center">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    {{ $company->email }}
                </div>
                @endif
                @if($company->phone)
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200 transition-colors">
                    <div class="w-8 flex justify-center">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                    {{ $company->phone }}
                </div>
                @endif
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200 transition-colors">
                    <div class="w-8 flex justify-center">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    {{ $company->currency ?? 'KES' }}
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-6 mt-6 border-t border-gray-100 dark:border-[#2A2A2A]">
                @if($company->id !== $activeCompany?->id)
                <form method="POST" action="{{ route('user.company.switch') }}" class="col-span-2">
                    @csrf
                    <input type="hidden" name="company_id" value="{{ $company->id }}">
                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-[#333333] shadow-sm text-xs font-bold rounded-xl text-gray-700 dark:text-white bg-white dark:bg-[#222222] hover:bg-gray-50 dark:hover:bg-[#111111] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 uppercase tracking-widest transition-colors">
                        Switch
                    </button>
                </form>
                @endif

                <a href="{{ route('user.companies.edit', $company->id) }}" class="{{ $company->id === $activeCompany?->id ? 'col-span-2' : '' }}">
                    <button class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-[#333333] shadow-sm text-xs font-bold rounded-xl text-gray-700 dark:text-white bg-white dark:bg-[#222222] hover:bg-gray-50 dark:hover:bg-[#111111] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 uppercase tracking-widest transition-colors">
                        Edit
                    </button>
                </a>

                @if($companies->count() > 1 && $company->id !== $activeCompany?->id)
                <form method="POST" action="{{ route('user.companies.destroy', $company->id) }}" onsubmit="return confirm('Are you sure you want to delete this company? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-xs font-bold rounded-xl text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 uppercase tracking-widest transition-colors">
                        Delete
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-12 text-center shadow-sm">
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-[#9A9A9A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No companies</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-[#9A9A9A]">Get started by creating a new company.</p>
        <div class="mt-6">
            <a href="{{ route('user.companies.create') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 uppercase tracking-wider">
                New Company
            </a>
        </div>
    </div>
    @endif
</div>
@endsection