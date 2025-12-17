<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Home') - {{ config('app.name', 'InvoiceHub') }}</title>
    @stack('meta')
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ route('favicon') }}" type="image/x-icon">
    
    <!-- Inter Font - Modern, professional typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @stack('scripts')
</head>
<body class="bg-slate-50">
    <!-- Sticky Navigation -->
    <x-navigation.nav-sticky
        :links="[
            ['text' => 'Home', 'href' => route('home')],
            ['text' => 'Invoicing Workflow', 'href' => route('home') . '#invoicing-workflow'],
            ['text' => 'About', 'href' => route('about')],
        ]"
        ctaText="Sign Up"
        :ctaHref="route('register')"
    />

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
    <footer class="bg-white border-t border-slate-200 mt-16">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- InvoiceHub Info -->
                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">InvoiceHub</h3>
                    <p class="text-sm text-slate-600 max-w-md mb-4">
                        Professional invoice management software for Kenyan businesses. Create, send, and track invoices effortlessly.
                    </p>
                    <div class="flex items-center gap-4 text-xs text-slate-500">
                        <span>Made in Kenya for Kenyan Businesses</span>
                    </div>
                </div>
                
                <!-- Product Links -->
                <div>
                    <h4 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-4">Product</h4>
                    <ul class="space-y-2">
                        <li><a href="#integrations" class="text-sm text-slate-600 hover:text-blue-600 transition-colors">Integrations</a></li>
                    </ul>
                </div>
                
                <!-- Company Links -->
                <div>
                    <h4 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-4">Company</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('about') }}" class="text-sm text-slate-600 hover:text-blue-600 transition-colors">About</a></li>
                        <li><a href="{{ route('login') }}" class="text-sm text-slate-600 hover:text-blue-600 transition-colors">Login</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Security Badges -->
            <div class="mt-8 pt-8 border-t border-slate-200">
                <div class="flex flex-wrap items-center justify-center gap-6 mb-6">
                    <x-trust.trust-badge text="KRA eTIMS Compliant" icon="shield-check" size="sm" />
                    <x-trust.trust-badge text="M-PESA Ready" icon="check-circle" size="sm" />
                    <x-trust.trust-badge text="Bank-Level Security" icon="lock-closed" size="sm" />
                    <x-trust.trust-badge text="GDPR Ready" icon="shield-check" size="sm" />
                </div>
                
                <div class="text-center mb-4">
                    <p class="text-sm text-slate-600 mb-2">
                        Trusted by 500+ Kenyan businesses
                    </p>
                    <div class="flex items-center justify-center gap-2 text-xs text-slate-500">
                        <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span>4.8/5 average rating</span>
                    </div>
                </div>
                
                <p class="text-center text-sm text-slate-600">
                    &copy; {{ date('Y') }} InvoiceHub. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
