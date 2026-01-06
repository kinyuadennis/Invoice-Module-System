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

<body class="bg-slate-50 dark:bg-[#0D0D0D]">
    <!-- Sticky Navigation -->
    <x-navigation.nav-sticky
        :links="[
            ['text' => 'Home', 'href' => route('home')],
            ['text' => 'Invoicing Workflow', 'href' => route('home') . '#invoicing-workflow'],
            ['text' => 'About', 'href' => route('about')],
        ]"
        ctaText="Sign Up"
        :ctaHref="route('register')" />

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
</body>

</html>