<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') - {{ config('app.name', 'Invoice Module') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h1 class="text-center text-3xl font-bold text-indigo-600">InvoiceHub</h1>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                @yield('heading', 'Welcome')
            </h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <x-card>
                @if(session('status'))
                    <x-alert type="success" class="mb-4">{{ session('status') }}</x-alert>
                @endif

                @if(session('error'))
                    <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
                @endif

                @yield('content')
            </x-card>
        </div>
    </div>
</body>
</html>

