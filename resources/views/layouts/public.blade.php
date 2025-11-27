<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Home') - {{ config('app.name', 'InvoiceHub') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <!-- Sticky Navigation -->
    <nav class="sticky top-0 z-50 bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-indigo-600 hover:text-indigo-700 transition-colors">InvoiceHub</a>
                </div>
                <div class="flex items-center space-x-1 sm:space-x-4">
                    <a href="#features" class="hidden sm:block text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">Features</a>
                    <a href="{{ route('pricing') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">Pricing</a>
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-700 px-3 py-2 rounded-md text-sm font-medium">Admin</a>
                        @else
                            <a href="{{ route('user.dashboard') }}" class="text-indigo-600 hover:text-indigo-700 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">Login</a>
                        <a href="{{ route('register') }}" class="bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2 rounded-md text-sm font-medium transition-colors shadow-sm">Sign Up</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash messages -->
    @if(session('success'))
        <x-alert type="success" class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">{{ session('success') }}</x-alert>
    @endif

    @if(session('error'))
        <x-alert type="error" class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">{{ session('error') }}</x-alert>
    @endif

    <!-- Main content -->
    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">InvoiceHub</h3>
                    <p class="text-sm text-gray-600 max-w-md">
                        Professional invoice management software for Kenyan businesses. Create, send, and track invoices effortlessly.
                    </p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Product</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('about') }}" class="text-sm text-gray-600 hover:text-indigo-600">Features</a></li>
                        <li><a href="{{ route('pricing') }}" class="text-sm text-gray-600 hover:text-indigo-600">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Company</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('about') }}" class="text-sm text-gray-600 hover:text-indigo-600">About</a></li>
                        <li><a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-indigo-600">Login</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-200">
                <p class="text-center text-sm text-gray-600">
                    &copy; {{ date('Y') }} InvoiceHub. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
