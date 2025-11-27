<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Invoice Module'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="hidden lg:flex lg:flex-shrink-0">
            <div class="flex flex-col w-64">
                <div class="flex flex-col flex-grow bg-white border-r border-gray-200 pt-5 pb-4 overflow-y-auto">
                    <!-- Logo -->
                    <div class="flex items-center flex-shrink-0 px-4">
                        <a href="{{ route('user.dashboard') }}" class="text-2xl font-bold text-indigo-600">InvoiceHub</a>
                    </div>
                    
                    <!-- Navigation -->
                    <nav class="mt-5 flex-1 px-2 space-y-1">
                        <x-nav-link href="{{ route('user.dashboard') }}" :active="request()->routeIs('user.dashboard')">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Dashboard
                        </x-nav-link>
                        
                        <x-nav-link href="{{ route('user.invoices.index') }}" :active="request()->routeIs('user.invoices.*')">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Invoices
                        </x-nav-link>
                        
                        <x-nav-link href="{{ route('user.payments.index') }}" :active="request()->routeIs('user.payments.*')">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Payments
                        </x-nav-link>
                        
                        <x-nav-link href="{{ route('user.profile') }}" :active="request()->routeIs('user.profile')">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </x-nav-link>
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
                                <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-medium">
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
        <div class="relative flex-1 flex flex-col max-w-xs w-full bg-white">
            <div class="absolute top-0 right-0 -mr-12 pt-2">
                <button @click="open = false" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex-1 h-0 pt-5 pb-4 overflow-y-auto">
                <div class="flex-shrink-0 flex items-center px-4">
                    <h1 class="text-2xl font-bold text-indigo-600">InvoiceHub</h1>
                </div>
                <nav class="mt-5 px-2 space-y-1">
                    <x-nav-link href="{{ route('user.dashboard') }}" :active="request()->routeIs('user.dashboard')">Dashboard</x-nav-link>
                    <x-nav-link href="{{ route('user.invoices.index') }}" :active="request()->routeIs('user.invoices.*')">Invoices</x-nav-link>
                    <x-nav-link href="{{ route('user.payments.index') }}" :active="request()->routeIs('user.payments.*')">Payments</x-nav-link>
                    <x-nav-link href="{{ route('user.profile') }}" :active="request()->routeIs('user.profile')">Profile</x-nav-link>
                </nav>
            </div>
        </div>
    </div>
</body>
</html>

