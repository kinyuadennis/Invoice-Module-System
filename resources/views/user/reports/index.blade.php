@extends('layouts.user')

@section('title', 'Reports')

@section('content')
<div class="space-y-6">
    <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Reports & Analytics</h1>
        <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">View detailed reports and export data</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Revenue Report -->
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm hover:border-blue-500/30 transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Revenue Report</h2>
                <div class="p-2 bg-green-100 dark:bg-green-500/10 rounded-xl group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] mb-6 h-10">View revenue trends, top clients, and monthly breakdowns</p>
            <a href="{{ route('user.reports.revenue') }}" class="block w-full px-4 py-2.5 bg-indigo-600 text-white font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-indigo-700 text-center transition-colors shadow-lg shadow-indigo-500/20">
                View Report
            </a>
        </div>

        <!-- Invoice Report -->
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm hover:border-blue-500/30 transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Invoice Report</h2>
                <div class="p-2 bg-blue-100 dark:bg-blue-500/10 rounded-xl group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] mb-6 h-10">Analyze invoices by status, client, and date range</p>
            <a href="{{ route('user.reports.invoices') }}" class="block w-full px-4 py-2.5 bg-indigo-600 text-white font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-indigo-700 text-center transition-colors shadow-lg shadow-indigo-500/20">
                View Report
            </a>
        </div>

        <!-- Payment Report -->
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm hover:border-blue-500/30 transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Payment Report</h2>
                <div class="p-2 bg-purple-100 dark:bg-purple-500/10 rounded-xl group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] mb-6 h-10">Track payments, methods, and payment trends</p>
            <a href="{{ route('user.reports.payments') }}" class="block w-full px-4 py-2.5 bg-indigo-600 text-white font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-indigo-700 text-center transition-colors shadow-lg shadow-indigo-500/20">
                View Report
            </a>
        </div>

        <!-- Aging Report -->
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm hover:border-blue-500/30 transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Aging Report</h2>
                <div class="p-2 bg-amber-100 dark:bg-amber-500/10 rounded-xl group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] mb-6 h-10">Track outstanding invoices grouped by age (0-90+ days)</p>
            <a href="{{ route('user.reports.aging') }}" class="block w-full px-4 py-2.5 bg-indigo-600 text-white font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-indigo-700 text-center transition-colors shadow-lg shadow-indigo-500/20">
                View Report
            </a>
        </div>

        <!-- Profit & Loss Statement -->
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm hover:border-blue-500/30 transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Profit & Loss</h2>
                <div class="p-2 bg-indigo-100 dark:bg-indigo-500/10 rounded-xl group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] mb-6 h-10">View revenue, expenses, and profit/loss analysis</p>
            <a href="{{ route('user.reports.profit-loss') }}" class="block w-full px-4 py-2.5 bg-indigo-600 text-white font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-indigo-700 text-center transition-colors shadow-lg shadow-indigo-500/20">
                View Report
            </a>
        </div>

        <!-- Expense Breakdown -->
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm hover:border-blue-500/30 transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Expense Breakdown</h2>
                <div class="p-2 bg-red-100 dark:bg-red-500/10 rounded-xl group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] mb-6 h-10">Analyze expenses by category, payment method, and trends</p>
            <a href="{{ route('user.reports.expenses') }}" class="block w-full px-4 py-2.5 bg-indigo-600 text-white font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-indigo-700 text-center transition-colors shadow-lg shadow-indigo-500/20">
                View Report
            </a>
        </div>
    </div>
</div>
@endsection