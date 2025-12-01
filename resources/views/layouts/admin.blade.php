<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - {{ config('app.name', 'InvoiceHub') }}</title>
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
                        <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold text-white">InvoiceHub <span class="text-xs text-slate-400">Admin</span></a>
                    </div>
                    
                    <!-- Navigation -->
                    <nav class="mt-5 flex-1 px-2 space-y-1">
                        <a href="{{ route('home') }}" class="text-slate-300 hover:bg-slate-800 hover:text-white border-transparent group flex items-center px-2 py-2 text-sm font-medium border-l-4">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            🏠 Home
                        </a>
                        
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-transparent' }} group flex items-center px-2 py-2 text-sm font-medium border-l-4">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Dashboard
                        </a>
                        
                        <a href="{{ route('admin.companies.index') }}" class="{{ request()->routeIs('admin.companies.*') ? 'bg-slate-800 text-white border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-transparent' }} group flex items-center px-2 py-2 text-sm font-medium border-l-4">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Companies
                        </a>
                        
                        <a href="{{ route('admin.clients.index') }}" class="{{ request()->routeIs('admin.clients.*') ? 'bg-slate-800 text-white border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-transparent' }} group flex items-center px-2 py-2 text-sm font-medium border-l-4">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Clients
                        </a>
                        
                        <a href="{{ route('admin.invoices.index') }}" class="{{ request()->routeIs('admin.invoices.*') ? 'bg-slate-800 text-white border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-transparent' }} group flex items-center px-2 py-2 text-sm font-medium border-l-4">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            All Invoices
                        </a>
                        
                        <a href="{{ route('admin.payments.index') }}" class="{{ request()->routeIs('admin.payments.*') ? 'bg-slate-800 text-white border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-transparent' }} group flex items-center px-2 py-2 text-sm font-medium border-l-4">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            All Payments
                        </a>
                        
                        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-transparent' }} group flex items-center px-2 py-2 text-sm font-medium border-l-4">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Users
                        </a>
                        
                        <a href="{{ route('admin.platform-fees.index') }}" class="{{ request()->routeIs('admin.platform-fees.*') ? 'bg-slate-800 text-white border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-transparent' }} group flex items-center px-2 py-2 text-sm font-medium border-l-4">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Platform Fees
                        </a>
                        
                        <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'bg-slate-800 text-white border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-transparent' }} group flex items-center px-2 py-2 text-sm font-medium border-l-4">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Settings
                        </a>
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
                        <a href="{{ route('user.dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">User Area</a>
                        <div class="relative">
                            <button @click="open = !open" class="flex items-center space-x-3 text-sm focus:outline-none">
                                <div class="h-8 w-8 rounded-full bg-slate-700 flex items-center justify-center text-white font-medium">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                                </div>
                                <span class="hidden md:block text-gray-700 font-medium">{{ auth()->user()->name ?? 'Admin' }}</span>
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                                <a href="{{ route('user.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">User Dashboard</a>
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
                    <h1 class="text-2xl font-bold text-white">InvoiceHub Admin</h1>
                </div>
                <nav class="mt-5 px-2 space-y-1">
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium">Dashboard</a>
                    <a href="{{ route('admin.companies.index') }}" class="{{ request()->routeIs('admin.companies.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium">Companies</a>
                    <a href="{{ route('admin.clients.index') }}" class="{{ request()->routeIs('admin.clients.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium">Clients</a>
                    <a href="{{ route('admin.invoices.index') }}" class="{{ request()->routeIs('admin.invoices.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium">Invoices</a>
                    <a href="{{ route('admin.payments.index') }}" class="{{ request()->routeIs('admin.payments.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium">Payments</a>
                    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium">Users</a>
                    <a href="{{ route('admin.platform-fees.index') }}" class="{{ request()->routeIs('admin.platform-fees.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium">Platform Fees</a>
                    <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium">Settings</a>
                </nav>
            </div>
        </div>
    </div>
</body>
</html>
