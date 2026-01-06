@extends('layouts.guest')

@section('title', '500 - Server Error')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <h1 class="text-9xl font-bold text-gray-200">500</h1>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">Server Error</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                Something went wrong on our end. We're working to fix it.
            </p>
        </div>
        <div class="mt-8 space-y-4">
            <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Go to Dashboard
            </a>
            <p class="text-xs text-gray-500">
                If this problem persists, please contact support.
            </p>
        </div>
    </div>
</div>
@endsection

