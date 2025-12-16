@extends('layouts.user')

@section('title', 'Reports')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
        <p class="mt-1 text-sm text-gray-600">View detailed reports and export data</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Revenue Report -->
        <x-card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Revenue Report</h2>
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm text-gray-600 mb-4">View revenue trends, top clients, and monthly breakdowns</p>
            <a href="{{ route('user.reports.revenue') }}" class="block w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-center">
                View Revenue Report
            </a>
        </x-card>

        <!-- Invoice Report -->
        <x-card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Invoice Report</h2>
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <p class="text-sm text-gray-600 mb-4">Analyze invoices by status, client, and date range</p>
            <a href="{{ route('user.reports.invoices') }}" class="block w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-center">
                View Invoice Report
            </a>
        </x-card>

        <!-- Payment Report -->
        <x-card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Payment Report</h2>
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <p class="text-sm text-gray-600 mb-4">Track payments, methods, and payment trends</p>
            <a href="{{ route('user.reports.payments') }}" class="block w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-center">
                View Payment Report
            </a>
        </x-card>
    </div>
</div>
@endsection

