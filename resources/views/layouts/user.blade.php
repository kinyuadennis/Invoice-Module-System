<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Invoice Module'))</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ route('favicon') }}" type="image/x-icon">
    <!-- Inter Font - Modern, professional typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="hidden lg:flex lg:flex-shrink-0">
            <div class="flex flex-col w-64">
                <div class="flex flex-col flex-grow bg-slate-900 text-white pt-5 pb-4 overflow-y-auto">
                    <!-- Logo -->
                    <div class="flex items-center flex-shrink-0 px-4">
                        <a href="{{ route('user.dashboard') }}" class="flex items-center space-x-2">
                            @if(auth()->user()->company?->logo)
                                <img src="{{ Storage::url(auth()->user()->company->logo) }}" alt="{{ auth()->user()->company->name }}" class="h-8 w-8 object-contain">
                            @endif
                            <span class="text-2xl font-bold text-white">
                                {{ auth()->user()->company?->name ?? 'InvoiceHub' }}
                            </span>
                        </a>
                    </div>
                    
                    <!-- Navigation -->
                    <nav class="mt-5 flex-1 px-2 space-y-1">
                        <a href="{{ route('home', ['view' => 'landing']) }}" class="{{ request()->routeIs('home') ? 'bg-slate-800 text-white border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-transparent' }} group flex items-center px-2 py-2 text-sm font-medium border-l-4">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Home
                        </a>
                        
                        <a href="{{ route('user.dashboard') }}" class="{{ request()->routeIs('user.dashboard') ? 'bg-slate-800 text-white border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-transparent' }} group flex items-center px-2 py-2 text-sm font-medium border-l-4">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Dashboard
                        </a>
                        
                        <a href="{{ route('user.invoices.index') }}" class="{{ request()->routeIs('user.invoices.*') ? 'bg-slate-800 text-white border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-transparent' }} group flex items-center px-2 py-2 text-sm font-medium border-l-4">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Invoices
                        </a>
                        
                        <a href="{{ route('user.payments.index') }}" class="{{ request()->routeIs('user.payments.*') ? 'bg-slate-800 text-white border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-transparent' }} group flex items-center px-2 py-2 text-sm font-medium border-l-4">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Payments
                        </a>
                        
                        <a href="{{ route('user.profile') }}" class="{{ request()->routeIs('user.profile') ? 'bg-slate-800 text-white border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-transparent' }} group flex items-center px-2 py-2 text-sm font-medium border-l-4">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </a>

                        @if(auth()->user()->company?->owner_user_id === auth()->id())
                            <a href="{{ route('user.company.settings') }}" class="{{ request()->routeIs('user.company.*') ? 'bg-slate-800 text-white border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-transparent' }} group flex items-center px-2 py-2 text-sm font-medium border-l-4">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Company Settings
                            </a>
                        @endif
                    </nav>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top navbar -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">
                    <!-- Mobile menu button -->
                    <button type="button" class="lg:hidden -ml-0.5 -mt-0.5 h-12 w-12 inline-flex items-center justify-center rounded-md text-gray-500 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" x-data @click="$dispatch('toggle-sidebar')">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="flex-1"></div>

                    <!-- User menu -->
                    <div class="flex items-center space-x-4" x-data="{ open: false }">
                        <div class="relative">
                            <button @click="open = !open" class="flex items-center space-x-3 text-sm focus:outline-none">
                                <div class="h-8 w-8 rounded-full bg-slate-700 flex items-center justify-center text-white font-medium">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                </div>
                                <span class="hidden md:block text-gray-700 font-medium">{{ auth()->user()->name ?? 'User' }}</span>
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                                <a href="{{ route('user.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign out</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <!-- Flash messages -->
                        @if(session('success'))
                            <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
                        @endif

                        @if(session('error'))
                            <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
                        @endif

                        @if(session('message'))
                            <x-alert type="info" class="mb-6">{{ session('message') }}</x-alert>
                        @endif

                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Mobile sidebar overlay -->
    <div x-data="{ open: false }" @toggle-sidebar.window="open = !open" x-show="open" x-cloak class="fixed inset-0 z-40 lg:hidden" style="display: none;">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="open = false"></div>
        <div class="relative flex-1 flex flex-col max-w-xs w-full bg-slate-900 text-white">
            <div class="absolute top-0 right-0 -mr-12 pt-2">
                <button @click="open = false" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex-1 h-0 pt-5 pb-4 overflow-y-auto">
                <div class="flex-shrink-0 flex items-center px-4">
                    @if(auth()->user()->company?->logo)
                        <img src="{{ Storage::url(auth()->user()->company->logo) }}" alt="{{ auth()->user()->company->name }}" class="h-8 w-8 object-contain mr-2">
                    @endif
                    <h1 class="text-2xl font-bold text-white">{{ auth()->user()->company?->name ?? 'InvoiceHub' }}</h1>
                </div>
                <nav class="mt-5 px-2 space-y-1">
                    <a href="{{ route('home', ['view' => 'landing']) }}" class="{{ request()->routeIs('home') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium">Home</a>
                    <a href="{{ route('user.dashboard') }}" class="{{ request()->routeIs('user.dashboard') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium">Dashboard</a>
                    <a href="{{ route('user.invoices.index') }}" class="{{ request()->routeIs('user.invoices.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium">Invoices</a>
                    <a href="{{ route('user.payments.index') }}" class="{{ request()->routeIs('user.payments.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium">Payments</a>
                    <a href="{{ route('user.profile') }}" class="{{ request()->routeIs('user.profile') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium">Profile</a>
                    @if(auth()->user()->company?->owner_user_id === auth()->id())
                        <a href="{{ route('user.company.settings') }}" class="{{ request()->routeIs('user.company.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium">Company Settings</a>
                    @endif
                </nav>
            </div>
        </div>
    </div>
</body>
</html>

