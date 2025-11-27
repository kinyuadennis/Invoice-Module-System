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
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-indigo-600">InvoiceHub</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('about') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">About</a>
                    <a href="{{ route('pricing') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Pricing</a>
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-700 px-3 py-2 rounded-md text-sm font-medium">Admin</a>
                        @else
                            <a href="{{ route('user.dashboard') }}" class="text-indigo-600 hover:text-indigo-700 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                        <a href="{{ route('register') }}" class="bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2 rounded-md text-sm font-medium">Sign Up</a>
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
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="text-center text-gray-600 text-sm">
                <p>&copy; {{ date('Y') }} InvoiceHub. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>

