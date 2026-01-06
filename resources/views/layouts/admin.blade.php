<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - {{ config('app.name', 'InvoiceHub') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ route('favicon') }}" type="image/x-icon">
    <!-- Inter Font - Modern, professional typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#0A0A0A] text-gray-900 dark:text-[#E5E5E5] antialiased selection:bg-blue-500/30">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="hidden lg:flex lg:flex-shrink-0">
            <div class="flex flex-col w-66">
                <div class="flex flex-col flex-grow bg-[#111111] dark:bg-[#111111] text-white pt-5 pb-4 overflow-y-auto border-r border-gray-200 dark:border-[#222222]">
                    <!-- Logo -->
                    <div class="flex items-center flex-shrink-0 px-6 mb-8">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-lg shadow-blue-500/20 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <span class="text-xl font-black tracking-tight text-white">InvoiceHub <span class="text-[10px] font-bold text-blue-400 uppercase tracking-widest bg-blue-500/10 px-1.5 py-0.5 rounded ml-1">Admin</span></span>
                        </a>
                    </div>

                    <!-- Navigation -->
                    <nav class="mt-2 flex-1 px-3 space-y-1">
                        <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-gray-400 hover:text-white hover:bg-[#1A1A1A] rounded-xl transition-all group">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Home
                        </a>

                        <div class="pt-4 pb-2 px-4 text-[10px] font-black text-gray-500 uppercase tracking-widest">Core System</div>

                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-gray-400 hover:text-white hover:bg-[#1A1A1A]' }} rounded-xl transition-all group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-gray-500 group-hover:text-blue-400' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            Dashboard
                        </a>

                        <a href="{{ route('admin.companies.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold {{ request()->routeIs('admin.companies.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-gray-400 hover:text-white hover:bg-[#1A1A1A]' }} rounded-xl transition-all group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.companies.*') ? 'text-white' : 'text-gray-500 group-hover:text-blue-400' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Companies
                        </a>

                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold {{ request()->routeIs('admin.users.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-gray-400 hover:text-white hover:bg-[#1A1A1A]' }} rounded-xl transition-all group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-gray-500 group-hover:text-blue-400' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Users
                        </a>

                        <div class="pt-6 pb-2 px-4 text-[10px] font-black text-gray-500 uppercase tracking-widest">Financials</div>

                        <a href="{{ route('admin.invoices.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold {{ request()->routeIs('admin.invoices.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-gray-400 hover:text-white hover:bg-[#1A1A1A]' }} rounded-xl transition-all group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.invoices.*') ? 'text-white' : 'text-gray-500 group-hover:text-blue-400' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            All Invoices
                        </a>

                        <a href="{{ route('admin.payments.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold {{ request()->routeIs('admin.payments.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-gray-400 hover:text-white hover:bg-[#1A1A1A]' }} rounded-xl transition-all group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.payments.*') ? 'text-white' : 'text-gray-500 group-hover:text-blue-400' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            All Payments
                        </a>

                        <div class="pt-6 pb-2 px-4 text-[10px] font-black text-gray-500 uppercase tracking-widest">Maintenance</div>

                        <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold {{ request()->routeIs('admin.audit-logs.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-gray-400 hover:text-white hover:bg-[#1A1A1A]' }} rounded-xl transition-all group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.audit-logs.*') ? 'text-white' : 'text-gray-500 group-hover:text-blue-400' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Audit Logs
                        </a>

                        <a href="{{ route('admin.system-settings.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold {{ request()->routeIs('admin.system-settings.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-gray-400 hover:text-white hover:bg-[#1A1A1A]' }} rounded-xl transition-all group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.system-settings.*') ? 'text-white' : 'text-gray-500 group-hover:text-blue-400' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            System Settings
                        </a>
                    </nav>

                    <!-- User Section at bottom -->
                    <div class="p-4 border-t border-gray-100 dark:border-[#222222]">
                        <div class="flex items-center gap-3 px-2 py-3 bg-[#1A1A1A] rounded-2xl border border-white/5">
                            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 font-black">
                                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-bold text-white truncate">{{ auth()->user()->name ?? 'Administrator' }}</div>
                                <div class="text-[10px] font-bold text-blue-400 uppercase tracking-widest">System Admin</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top navbar -->
            <header class="sticky top-0 z-40 bg-white/70 dark:bg-[#0A0A0A]/70 backdrop-blur-xl border-b border-gray-100 dark:border-[#222222]">
                <div class="flex items-center justify-between px-6 h-20">
                    <!-- Mobile menu button -->
                    <button type="button" class="lg:hidden -ml-0.5 h-12 w-12 inline-flex items-center justify-center rounded-xl text-gray-500 hover:text-gray-900 focus:outline-none" x-data @click="$dispatch('toggle-sidebar')">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="flex-1"></div>

                    <!-- User Actions -->
                    <div class="flex items-center gap-4">
                        <a href="{{ route('user.dashboard') }}" class="btn-ripple hidden md:inline-flex items-center px-4 py-2 text-xs font-bold text-gray-600 dark:text-[#9A9A9A] bg-gray-50 dark:bg-[#111111] border border-gray-200 dark:border-[#222222] rounded-xl hover:bg-gray-100 dark:hover:bg-[#181818] transition-all">
                            Switch to User Mode
                        </a>

                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-3 p-1 rounded-full hover:bg-gray-100 dark:hover:bg-[#111111] transition-all">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center text-white border-2 border-white dark:border-[#222222] shadow-xl">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                                </div>
                            </button>

                            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="absolute right-0 mt-3 w-56 bg-white dark:bg-[#111111] rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.3)] py-2 z-50 border border-gray-100 dark:border-[#222222]" style="display: none;">
                                <div class="px-4 py-3 border-b border-gray-50 dark:border-[#1A1A1A] mb-1">
                                    <p class="text-xs font-bold text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Authenticated as</p>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="{{ route('user.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-gray-600 dark:text-[#D4D4D4] hover:bg-gray-50 dark:hover:bg-[#1A1A1A] transition-all">
                                    User Dashboard
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-3 px-4 py-2.5 text-sm font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/10 transition-all">
                                        Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto bg-[#0A0A0A]">
                <div class="py-8">
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
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-[#2A2A2A] hover:text-white hover:shadow-lg hover:shadow-blue-500/10 hover:translate-x-1 transition-all duration-200' }} group flex items-center px-2 py-2 text-sm font-medium">Dashboard</a>
                    <a href="{{ route('admin.companies.index') }}" class="{{ request()->routeIs('admin.companies.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-[#2A2A2A] hover:text-white hover:shadow-lg hover:shadow-blue-500/10 hover:translate-x-1 transition-all duration-200' }} group flex items-center px-2 py-2 text-sm font-medium">Companies</a>
                    <a href="{{ route('admin.clients.index') }}" class="{{ request()->routeIs('admin.clients.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-[#2A2A2A] hover:text-white hover:shadow-lg hover:shadow-blue-500/10 hover:translate-x-1 transition-all duration-200' }} group flex items-center px-2 py-2 text-sm font-medium">Clients</a>
                    <a href="{{ route('admin.invoices.index') }}" class="{{ request()->routeIs('admin.invoices.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-[#2A2A2A] hover:text-white hover:shadow-lg hover:shadow-blue-500/10 hover:translate-x-1 transition-all duration-200' }} group flex items-center px-2 py-2 text-sm font-medium">Invoices</a>
                    <a href="{{ route('admin.payments.index') }}" class="{{ request()->routeIs('admin.payments.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-[#2A2A2A] hover:text-white hover:shadow-lg hover:shadow-blue-500/10 hover:translate-x-1 transition-all duration-200' }} group flex items-center px-2 py-2 text-sm font-medium">Payments</a>
                    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-[#2A2A2A] hover:text-white hover:shadow-lg hover:shadow-blue-500/10 hover:translate-x-1 transition-all duration-200' }} group flex items-center px-2 py-2 text-sm font-medium">Users</a>
                    <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-[#2A2A2A] hover:text-white hover:shadow-lg hover:shadow-blue-500/10 hover:translate-x-1 transition-all duration-200' }} group flex items-center px-2 py-2 text-sm font-medium">Settings</a>
                </nav>
            </div>
        </div>
    </div>
</body>

</html>