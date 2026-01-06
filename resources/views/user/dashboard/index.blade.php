@extends('layouts.user')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300 dark:text-gray-400">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <!-- Quick Actions -->
            <div class="flex flex-wrap items-center gap-2" id="dashboard-quick-actions">
                <a href="{{ route('user.invoices.create') }}" id="dashboard-new-invoice-btn" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-[#4DABF7] to-[#2563EB] rounded-xl hover:from-[#3B82F6] hover:to-[#1D4ED8] hover:shadow-lg hover:shadow-blue-500/40 hover:scale-105 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Invoice
                </a>
                @if(Schema::hasTable('estimates'))
                <a href="{{ route('user.estimates.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="hidden md:inline">New Estimate</span>
                </a>
                @endif
                @can('create', App\Models\Client::class)
                <a href="{{ route('user.clients.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <span class="hidden md:inline">New Client</span>
                </a>
                @endcan
                <a href="{{ route('user.payments.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="hidden md:inline">Record Payment</span>
                </a>
                @if(($stats['overdueCount'] ?? 0) > 0)
                <a href="{{ route('user.invoices.index', ['status' => 'overdue']) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="hidden md:inline">Chase Overdue</span>
                </a>
                @endif
                <a href="{{ route('user.reports.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span class="hidden md:inline">Reports</span>
                </a>
            </div>
            @if(isset($companies) && $companies->count() > 0)
            <a href="{{ route('user.companies.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors" title="Add another company">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden md:inline">Add Company</span>
            </a>
            @endif
        </div>
    </div>

    @if(!auth()->user()->onboarding_completed || (isset($companies) && $companies->count() === 0))
    <!-- Quick Setup Card -->
    <div class="bg-white dark:bg-[#252525] border border-blue-100 dark:border-[#333333] shadow-lg shadow-blue-500/5 rounded-xl p-6 relative overflow-hidden" id="dashboard-quick-setup">
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-full blur-3xl -mr-32 -mt-32 opacity-50 pointer-events-none"></div>
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Complete Your Setup</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 dark:text-gray-400 mb-4">
                    @if(!auth()->user()->onboarding_completed)
                    Finish setting up your account to get the most out of InvoiceHub.
                    @elseif(isset($companies) && $companies->count() === 0)
                    Create your first company to start invoicing.
                    @endif
                </p>
                <div class="flex gap-3">
                    @if(!auth()->user()->onboarding_completed)
                    <a href="{{ route('user.onboarding.index') }}" class="inline-flex items-center px-4 py-2 bg-[#2B6EF6] text-white text-sm font-medium rounded-lg hover:bg-[#2563EB] transition-colors">
                        Complete Setup
                    </a>
                    @endif
                    @if(isset($companies) && $companies->count() === 0)
                    <a href="{{ route('user.companies.create') }}" class="inline-flex items-center px-4 py-2 bg-[#2B6EF6] text-white text-sm font-medium rounded-lg hover:bg-[#2563EB] transition-colors">
                        Create Company
                    </a>
                    @endif
                </div>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600 dark:text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
    @endif

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-card padding="sm" class="interactive-card cursor-pointer" onclick="window.location.href='{{ route('user.invoices.index', ['status' => 'paid']) }}'">
            <div class="flex items-center">
                <div class="flex-shrink-0 icon-bg-blue p-3 rounded-xl">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] truncate">Total Revenue</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-[#F5F5F5] tracking-tight">KES {{ number_format($stats['totalRevenue'] ?? 0, 2) }}</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <x-card padding="sm" class="interactive-card cursor-pointer" onclick="window.location.href='{{ route('user.invoices.index', ['status' => 'sent']) }}'">
            <div class="flex items-center">
                <div class="flex-shrink-0 icon-bg-amber p-3 rounded-xl">
                    <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] truncate">Outstanding</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-[#F5F5F5] tracking-tight">KES {{ number_format($stats['outstanding'] ?? 0, 2) }}</dd>
                        <dd class="text-xs text-gray-500 dark:text-[#9A9A9A] mt-1">{{ $stats['outstandingCount'] ?? 0 }} invoice(s)</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <x-card padding="sm" class="interactive-card cursor-pointer" onclick="window.location.href='{{ route('user.invoices.index', ['status' => 'overdue']) }}'">
            <div class="flex items-center">
                <div class="flex-shrink-0 icon-bg-red p-3 rounded-xl">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] truncate">Overdue</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-[#F5F5F5] tracking-tight">{{ $stats['overdueCount'] ?? 0 }}</dd>
                        <dd class="text-xs text-red-600 dark:text-red-400 mt-1 font-medium">KES {{ number_format($stats['overdue'] ?? 0, 2) }}</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <x-card padding="sm" class="interactive-card cursor-pointer" onclick="window.location.href='{{ route('user.invoices.index', ['status' => 'paid']) }}'">
            <div class="flex items-center">
                <div class="flex-shrink-0 icon-bg-emerald p-3 rounded-xl">
                    <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] truncate">Paid Invoices</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-[#F5F5F5] tracking-tight">{{ $stats['paidCount'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Additional KPIs: Expenses & Cash Flow -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Expenses -->
        <x-card padding="sm" class="interactive-card cursor-pointer" onclick="window.location.href='{{ route('user.expenses.index') }}'">
            <div class="flex items-center">
                <div class="flex-shrink-0 icon-bg-red p-3 rounded-xl">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] truncate">Total Expenses</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-[#F5F5F5] tracking-tight">KES {{ number_format($expenseStats['total_expenses'] ?? 0, 2) }}</dd>
                        <dd class="text-xs text-gray-500 dark:text-[#9A9A9A] mt-1">{{ $expenseStats['expense_count'] ?? 0 }} expense(s)</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <!-- This Month Expenses -->
        <x-card padding="sm" class="interactive-card cursor-pointer" onclick="window.location.href='{{ route('user.expenses.index', ['date_range' => 'month']) }}'">
            <div class="flex items-center">
                <div class="flex-shrink-0 icon-bg-amber p-3 rounded-xl">
                    <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] truncate">This Month Expenses</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-[#F5F5F5] tracking-tight">KES {{ number_format($expenseStats['this_month_expenses'] ?? 0, 2) }}</dd>
                        <dd class="text-xs text-gray-500 dark:text-[#9A9A9A] mt-1">{{ $expenseStats['this_month_count'] ?? 0 }} expense(s)</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <!-- Net Cash Flow -->
        <x-card padding="sm" class="interactive-card">
            <div class="flex items-center">
                <div class="flex-shrink-0 {{ ($cashFlow['net_cash_flow'] ?? 0) >= 0 ? 'icon-bg-emerald' : 'icon-bg-red' }} p-3 rounded-xl">
                    <svg class="h-6 w-6 {{ ($cashFlow['net_cash_flow'] ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] truncate">Net Cash Flow</dt>
                        <dd class="text-lg font-semibold {{ ($cashFlow['net_cash_flow'] ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            KES {{ number_format($cashFlow['net_cash_flow'] ?? 0, 2) }}
                        </dd>
                        @if(isset($cashFlow['cash_flow_change']) && $cashFlow['cash_flow_change'] != 0)
                        <dd class="text-xs {{ $cashFlow['cash_flow_change'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }} mt-1 font-medium">
                            {{ $cashFlow['cash_flow_change'] >= 0 ? '+ ' : '' }}{{ number_format($cashFlow['cash_flow_change'], 1) }}% vs last month
                        </dd>
                        @endif
                    </dl>
                </div>
            </div>
        </x-card>

        <!-- Tax Deductible Expenses -->
        <x-card padding="sm" class="interactive-card cursor-pointer" onclick="window.location.href='{{ route('user.expenses.index', ['tax_deductible' => '1']) }}'">
            <div class="flex items-center">
                <div class="flex-shrink-0 icon-bg-purple p-3 rounded-xl">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] truncate">Tax Deductible</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-[#F5F5F5] tracking-tight">KES {{ number_format($expenseStats['tax_deductible'] ?? 0, 2) }}</dd>
                        <dd class="text-xs text-gray-500 dark:text-[#9A9A9A] mt-1">Eligible for tax deduction</dd>
                    </dl>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Additional Metrics KPI Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Average Invoice Value -->
        <x-card padding="sm" class="interactive-card">
            <div class="flex items-center">
                <div class="flex-shrink-0 icon-bg-indigo p-3 rounded-xl">
                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] truncate">Avg Invoice Value</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-[#F5F5F5] tracking-tight">KES {{ number_format($stats['averageInvoiceValue'] ?? 0, 2) }}</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <!-- Invoice Conversion Rate -->
        @if(isset($additionalMetrics) && ($additionalMetrics['totalEstimates'] ?? 0) > 0)
        <x-card padding="sm" class="interactive-card cursor-pointer" onclick="window.location.href='{{ route('user.estimates.index') }}'">
            <div class="flex items-center">
                <div class="flex-shrink-0 icon-bg-teal p-3 rounded-xl">
                    <svg class="h-6 w-6 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] truncate">Estimate Conversion</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-[#F5F5F5] tracking-tight">{{ number_format($additionalMetrics['invoiceConversionRate'] ?? 0, 1) }}%</dd>
                        <dd class="text-xs text-gray-500 dark:text-[#9A9A9A] mt-1">{{ $additionalMetrics['convertedEstimates'] ?? 0 }} / {{ $additionalMetrics['totalEstimates'] ?? 0 }} converted</dd>
                        <dd class="mt-3">
                            @php $conversionRate = min(100, $additionalMetrics['invoiceConversionRate'] ?? 0); @endphp
                            <div class="w-full bg-gray-100 dark:bg-[#2A2A2A] rounded-full h-1.5 overflow-hidden">
                                <div class="bg-teal-500 h-full rounded-full transition-all duration-1000" style="width: <?php echo $conversionRate; ?>%"></div>
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </x-card>
        @endif

        <!-- Payment Success Rate -->
        <x-card padding="sm" class="interactive-card">
            <div class="flex items-center">
                <div class="flex-shrink-0 {{ ($additionalMetrics['paymentSuccessRate'] ?? 0) >= 80 ? 'icon-bg-emerald' : (($additionalMetrics['paymentSuccessRate'] ?? 0) >= 60 ? 'icon-bg-amber' : 'icon-bg-red') }} rounded-xl p-3">
                    <svg class="h-6 w-6 {{ ($additionalMetrics['paymentSuccessRate'] ?? 0) >= 80 ? 'text-emerald-600 dark:text-emerald-400' : (($additionalMetrics['paymentSuccessRate'] ?? 0) >= 60 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A] truncate">Payment Success Rate</dt>
                        <dd class="text-2xl font-bold text-gray-900 dark:text-[#F5F5F5] tracking-tight">{{ number_format($additionalMetrics['paymentSuccessRate'] ?? 0, 1) }}%</dd>
                        <dd class="text-xs text-gray-500 dark:text-[#9A9A9A] mt-1">{{ $additionalMetrics['paidOnTimeCount'] ?? 0 }} paid on time</dd>
                        <dd class="mt-3">
                            @php $successRate = min(100, $additionalMetrics['paymentSuccessRate'] ?? 0); @endphp
                            <div class="w-full bg-gray-100 dark:bg-[#2A2A2A] rounded-full h-1.5 overflow-hidden">
                                <div class="{{ $successRate >= 80 ? 'bg-emerald-500' : ($successRate >= 60 ? 'bg-amber-500' : 'bg-red-500') }} h-full rounded-full transition-all duration-1000" style="width: <?php echo $successRate; ?>%"></div>
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Compliance Section -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- eTIMS Compliance Card -->
        <x-card>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-[#F5F5F5]">eTIMS Compliance</h3>
                <span class="badge-{{ ($complianceData['etimsComplianceRate'] ?? 0) >= 95 ? 'emerald' : (($complianceData['etimsComplianceRate'] ?? 0) >= 80 ? 'amber' : 'red') }}">
                    {{ number_format($complianceData['etimsComplianceRate'] ?? 0, 1) }}%
                </span>
            </div>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-500 dark:text-[#9A9A9A]">Compliance Rate</span>
                        <span class="font-semibold text-gray-900 dark:text-[#F5F5F5]">{{ number_format($complianceData['etimsComplianceRate'] ?? 0, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-[#2A2A2A] rounded-full h-2 overflow-hidden">
                        <div class="{{ ($complianceData['etimsComplianceRate'] ?? 0) >= 95 ? 'bg-emerald-500' : (($complianceData['etimsComplianceRate'] ?? 0) >= 80 ? 'bg-amber-500' : 'bg-red-500') }} h-full rounded-full transition-all duration-1000" style="width: {{ min(100, $complianceData['etimsComplianceRate'] ?? 0) }}%"></div>
                    </div>
                </div>
                <div class="flex justify-between text-sm text-gray-600 dark:text-[#D4D4D4] pt-4 border-t border-gray-100 dark:border-[#2A2A2A]">
                    <div class="flex flex-col">
                        <span class="text-xs text-gray-400 dark:text-[#9A9A9A]">Submitted</span>
                        <span class="font-bold text-gray-900 dark:text-[#F5F5F5]">{{ $complianceData['etimsSubmittedCount'] ?? 0 }}</span>
                    </div>
                    <div class="flex flex-col text-right">
                        <span class="text-xs text-gray-400 dark:text-[#9A9A9A]">Total Target</span>
                        <span class="font-bold text-gray-900 dark:text-[#F5F5F5]">{{ $complianceData['totalInvoicesForCompliance'] ?? 0 }}</span>
                    </div>
                </div>
                @if(($complianceData['recentEtimsSubmissions'] ?? 0) > 0)
                <div class="flex items-center gap-2 px-3 py-2 bg-emerald-500/5 dark:bg-emerald-400/5 border border-emerald-500/10 dark:border-emerald-400/10 rounded-xl">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400">
                        {{ $complianceData['recentEtimsSubmissions'] }} submitted in last 7 days
                    </span>
                </div>
                @endif
            </div>
        </x-card>

        <!-- Fraud & Bank Reconciliation Card -->
        <x-card>
            <div class="space-y-6">
                <!-- Fraud Indicators -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-[#F5F5F5] mb-4">Risk Assessment</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-gray-50 dark:bg-[#111111] border border-gray-100 dark:border-[#2A2A2A] rounded-2xl">
                            <div class="text-3xl font-bold {{ ($fraudIndicators['flagged_count'] ?? 0) > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-[#F5F5F5]' }}">
                                {{ $fraudIndicators['flagged_count'] ?? 0 }}
                            </div>
                            <div class="text-xs font-semibold text-gray-400 dark:text-[#9A9A9A] uppercase tracking-wider mt-1">Flagged</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-[#111111] border border-gray-100 dark:border-[#2A2A2A] rounded-2xl">
                            <div class="text-3xl font-bold text-gray-900 dark:text-[#F5F5F5]">{{ number_format($fraudIndicators['average_fraud_score'] ?? 0, 1) }}</div>
                            <div class="text-xs font-semibold text-gray-400 dark:text-[#9A9A9A] uppercase tracking-wider mt-1">Avg Score</div>
                        </div>
                    </div>
                    @if(($fraudIndicators['requires_review'] ?? 0) > 0)
                    <div class="mt-4 flex items-center gap-3 p-3 bg-amber-500/5 dark:bg-amber-400/5 border border-amber-500/10 dark:border-amber-400/10 rounded-xl">
                        <svg class="h-5 w-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span class="text-sm font-medium text-amber-700 dark:text-amber-400">
                            {{ $fraudIndicators['requires_review'] }} payments require manual review
                        </span>
                    </div>
                    @endif
                </div>

                <!-- Bank Reconciliation -->
                @if(($bankReconciliationStatus['is_enabled'] ?? false))
                <div class="pt-6 border-t border-gray-100 dark:border-[#2A2A2A]">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-[#F5F5F5]">Bank Reconciliation</h3>
                        <span class="badge-blue">{{ number_format($bankReconciliationStatus['reconciled_percentage'] ?? 0, 1) }}% matched</span>
                    </div>
                    <div class="space-y-3">
                        <div class="w-full bg-gray-100 dark:bg-[#2A2A2A] rounded-full h-1.5 overflow-hidden">
                            <div class="bg-blue-500 h-full rounded-full transition-all duration-1000" style="width: {{ min(100, $bankReconciliationStatus['reconciled_percentage'] ?? 0) }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs font-medium text-gray-500 dark:text-[#9A9A9A]">
                            <span>{{ $bankReconciliationStatus['matched_payments'] ?? 0 }} reconciled items</span>
                            <span>{{ $bankReconciliationStatus['pending_transactions'] ?? 0 }} pending</span>
                        </div>
                        @if(($bankReconciliationStatus['pending_transactions'] ?? 0) > 0)
                        <a href="{{ route('user.bank-reconciliations.index') }}" class="inline-flex items-center mt-2 text-sm font-bold text-[#2B6EF6] hover:text-[#2563EB] group transition-colors">
                            Complete reconciliation
                            <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </x-card>
    </div>

    <!-- Duplicate Detection Alert -->
    @if(isset($complianceData) && ($complianceData['duplicateCount'] ?? 0) > 0)
    <x-alert type="warning">
        <div class="flex items-center justify-between">
            <div>
                <strong>Duplicate Detection:</strong> {{ $complianceData['duplicateCount'] }} potential duplicate invoice(s) detected.
            </div>
            <a href="{{ route('user.invoices.index') }}" class="ml-4 text-sm font-medium underline">Review Invoices</a>
        </div>
    </x-alert>
    @endif

    <!-- Status Distribution & Quick Filters -->
    @php
    $hasStatusData = isset($statusDistribution) && count($statusDistribution) > 0 && collect($statusDistribution)->sum('count') > 0;
    @endphp
    @if($hasStatusData)
    <x-card>
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-[#F5F5F5]">Invoice Status Overview</h2>
                <p class="text-sm text-gray-500 dark:text-[#9A9A9A] mt-1">Real-time distribution of your invoicing output</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('user.invoices.index', ['status' => 'draft']) }}" class="btn-ripple px-5 py-2.5 text-sm font-bold text-gray-700 dark:text-[#D4D4D4] bg-gray-50 dark:bg-[#111111] border border-gray-200 dark:border-[#2A2A2A] rounded-xl hover:bg-gray-100 dark:hover:bg-[#181818] transition-all">
                    Draft
                </a>
                <a href="{{ route('user.invoices.index', ['status' => 'sent']) }}" class="btn-ripple px-5 py-2.5 text-sm font-bold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-400/5 border border-blue-100 dark:border-blue-400/10 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-400/10 transition-all">
                    Sent
                </a>
                <a href="{{ route('user.invoices.index', ['status' => 'paid']) }}" class="btn-ripple px-5 py-2.5 text-sm font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-400/5 border border-emerald-100 dark:border-emerald-400/10 rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-400/10 transition-all">
                    Paid
                </a>
                <a href="{{ route('user.invoices.index', ['status' => 'overdue']) }}" class="btn-ripple px-5 py-2.5 text-sm font-bold text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-400/5 border border-red-100 dark:border-red-400/10 rounded-xl hover:bg-red-100 dark:hover:bg-red-400/10 transition-all">
                    Overdue
                </a>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Status Pie Chart - More Prominent -->
            <div class="lg:col-span-2" style="position: relative; height: 300px; max-width: 100%;">
                <canvas id="statusChart"></canvas>
            </div>
            <!-- Status Cards List -->
            <div class="space-y-4">
                @foreach($statusDistribution as $status)
                @if($status['count'] > 0)
                <a href="{{ route('user.invoices.index', ['status' => strtolower($status['name'])]) }}" class="interactive-card block p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ str_replace(')', ', 0.1)', $status['bgColor'] ?? 'rgba(0,0,0)') }}">
                                <div class="w-3 h-3 rounded-full" style="background-color: {{ $status['color'] }}"></div>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $status['name'] }}</div>
                                <div class="text-xs font-medium text-gray-500 dark:text-[#9A9A9A]">{{ $status['percentage'] }}% of volume</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-black tracking-tight" style="color: {{ $status['color'] }}">
                                {{ $status['count'] }}
                            </div>
                            <div class="text-[10px] font-bold text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Invoices</div>
                        </div>
                    </div>
                </a>
                @endif
                @endforeach
            </div>
        </div>
    </x-card>
    @endif

    <!-- Business Insights -->
    @if(isset($insights))
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Trends Chart -->
        @if(isset($insights['revenue_trends']) && count($insights['revenue_trends']) > 0)
        <x-card>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-[#F5F5F5]">Revenue Trends</h2>
                <span class="text-xs font-semibold text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Last 12 Months</span>
            </div>
            <div class="chart-container" style="position: relative; height: 320px; max-width: 100%;">
                <canvas id="revenueChart"></canvas>
            </div>
        </x-card>
        @endif

        <!-- Invoice Aging Report -->
        @if(isset($insights['invoice_aging']))
        <x-card>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-[#F5F5F5]">Invoice Aging Report</h2>
                <div class="icon-bg-amber/10 p-2 rounded-lg">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="space-y-3">
                @foreach($insights['invoice_aging'] as $ageGroup => $data)
                @if($data['count'] > 0)
                <div class="group flex items-center justify-between p-4 bg-gray-50 dark:bg-[#111111] border border-gray-100 dark:border-[#2A2A2A] rounded-2xl hover:border-blue-500/30 transition-all">
                    <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-[#F5F5F5]">{{ $ageGroup }} Days</p>
                        <p class="text-xs font-medium text-gray-500 dark:text-[#9A9A9A]">{{ $data['count'] }} outstanding invoice(s)</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-black text-gray-900 dark:text-[#F5F5F5]">KES {{ number_format($data['amount'], 2) }}</p>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </x-card>
        @endif
    </div>

    <!-- DSO & Top Clients -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- DSO Metrics -->
        @if(isset($insights['dso']))
        <x-card>
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-lg font-bold text-gray-900 dark:text-[#F5F5F5]">Days Sales Outstanding</h2>
                <div class="icon-bg-blue/10 p-2 rounded-lg">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">30-Day DSO</span>
                    <span class="text-3xl font-black text-gray-900 dark:text-[#F5F5F5]">{{ number_format($insights['dso'], 1) }}</span>
                    <span class="text-xs font-medium text-gray-500 dark:text-[#9A9A9A]">days average</span>
                </div>
                @if(isset($insights['dso_90']))
                <div class="flex flex-col md:border-l border-gray-100 dark:border-[#2A2A2A] md:pl-6">
                    <span class="text-xs font-bold text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">90-Day DSO</span>
                    <span class="text-3xl font-black text-gray-900 dark:text-[#F5F5F5]">{{ number_format($insights['dso_90'], 1) }}</span>
                    <span class="text-xs font-medium text-gray-500 dark:text-[#9A9A9A]">stability index</span>
                </div>
                @endif
                @if(isset($insights['avg_payment_time']))
                <div class="flex flex-col md:border-l border-gray-100 dark:border-[#2A2A2A] md:pl-6">
                    <span class="text-xs font-bold text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Avg Pay Time</span>
                    <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($insights['avg_payment_time'], 1) }}</span>
                    <span class="text-xs font-medium text-gray-500 dark:text-[#9A9A9A]">calendar days</span>
                </div>
                @endif
            </div>
            <div class="mt-8 p-4 bg-blue-500/5 border border-blue-500/10 rounded-2xl">
                <p class="text-xs font-medium text-blue-700 dark:text-blue-400 leading-relaxed">
                    <span class="font-bold">Pro Tip:</span> Your DSO of {{ number_format($insights['dso'], 1) }} days is
                    @if($insights['dso'] < 30) <span class="text-emerald-600 font-bold">excellent</span>. Most SaaS businesses aim for sub-45 days.
                        @else <span class="text-amber-600 font-bold">trending high</span>. Consider automated follow-ups. @endif
                </p>
            </div>
        </x-card>
        @endif

        <!-- Top Clients -->
        @if(isset($insights['top_clients']) && count($insights['top_clients']) > 0)
        <x-card>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-[#F5F5F5]">Top Clients</h2>
                <div class="icon-bg-purple/10 p-2 rounded-lg">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="chart-container" style="position: relative; height: 320px; max-width: 100%;">
                <canvas id="topClientsChart"></canvas>
            </div>
        </x-card>
        @endif
    </div>
    @endif

    <!-- Additional Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Payment Method Breakdown -->
        @if(isset($paymentMethodBreakdown) && count($paymentMethodBreakdown['breakdown'] ?? []) > 0)
        <x-card>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-[#F5F5F5]">Payment Methods</h2>
                <div class="icon-bg-emerald/10 p-2 rounded-lg">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
            </div>
            <div class="chart-container" style="position: relative; height: 320px; max-width: 100%;">
                <canvas id="paymentMethodChart"></canvas>
            </div>
        </x-card>
        @endif

        <!-- Cash Flow Forecast -->
        @if(isset($cashFlowForecast) && count($cashFlowForecast) > 0)
        <x-card>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-[#F5F5F5]">Cash Flow Forecast</h2>
                <span class="text-xs font-semibold text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Next 3 Months</span>
            </div>
            <div class="chart-container" style="position: relative; height: 320px; max-width: 100%;">
                <canvas id="cashFlowForecastChart"></canvas>
            </div>
        </x-card>
        @endif
    </div>

    <!-- Multi-Company Overview -->
    <!-- Multi-Company Overview -->
    @if(isset($multiCompanyOverview) && isset($multiCompanyOverview['companies']) && count($multiCompanyOverview['companies']) > 1)
    <x-card>
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-[#F5F5F5]">Multi-Entity Overview</h2>
                <p class="text-sm text-gray-500 dark:text-[#9A9A9A] mt-1">Consolidated performance across your business portfolio</p>
            </div>
            <a href="{{ route('user.companies.index') }}" class="btn-ripple inline-flex items-center px-4 py-2 bg-gray-50 dark:bg-[#111111] border border-gray-200 dark:border-[#2A2A2A] rounded-xl text-sm font-bold text-gray-700 dark:text-[#D4D4D4] hover:bg-gray-100 dark:hover:bg-[#181818] transition-all">
                Manage Entities
            </a>
        </div>
        <div class="overflow-x-auto -mx-6 px-6">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="text-left font-bold uppercase tracking-widest text-[10px] text-gray-400 dark:text-[#9A9A9A]">Company</th>
                        <th class="text-right font-bold uppercase tracking-widest text-[10px] text-gray-400 dark:text-[#9A9A9A]">Total Revenue</th>
                        <th class="text-right font-bold uppercase tracking-widest text-[10px] text-gray-400 dark:text-[#9A9A9A]">Outstanding</th>
                        <th class="text-right font-bold uppercase tracking-widest text-[10px] text-gray-400 dark:text-[#9A9A9A]">Overdue</th>
                        <th class="text-right font-bold uppercase tracking-widest text-[10px] text-gray-400 dark:text-[#9A9A9A]">DSO</th>
                        <th class="text-center font-bold uppercase tracking-widest text-[10px] text-gray-400 dark:text-[#9A9A9A]">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($multiCompanyOverview['companies'] as $company)
                    <tr class="{{ $company['isActive'] ? 'bg-blue-500/5' : '' }}">
                        <td class="font-bold text-gray-900 dark:text-[#F5F5F5]">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 dark:from-[#2A2A2A] dark:to-[#1F1F1F] flex items-center justify-center text-xs font-black text-gray-600 dark:text-[#B8B8B8]">
                                    {{ substr($company['name'], 0, 1) }}
                                </div>
                                {{ $company['name'] }}
                                @if($company['isActive'])
                                <span class="w-2 h-2 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.5)]"></span>
                                @endif
                            </div>
                        </td>
                        <td class="text-right font-mono font-bold text-gray-900 dark:text-[#F5F5F5]">KES {{ number_format($company['totalRevenue'], 0) }}</td>
                        <td class="text-right font-mono text-gray-600 dark:text-[#D4D4D4]">KES {{ number_format($company['outstanding'], 0) }}</td>
                        <td class="text-right font-mono font-bold {{ $company['overdue'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}">KES {{ number_format($company['overdue'], 0) }}</td>
                        <td class="text-right font-bold text-gray-900 dark:text-[#F5F5F5]">{{ number_format($company['dso'], 1) }}d</td>
                        <td class="text-center">
                            <span class="badge-{{ $company['isActive'] ? 'blue' : 'gray' }}">
                                {{ $company['isActive'] ? 'Primary' : 'Secondary' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
    @endif

    <!-- AI Insights & Predictions -->
    @if(isset($aiInsights))
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Prediction -->
        @if(($aiInsights['predictedNextMonthRevenue'] ?? 0) > 0)
        <x-card class="dark:bg-[#252525] dark:border dark:border-[#333333] rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.3)]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Revenue Prediction</h3>
                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $aiInsights['revenueTrend'] === 'increasing' ? 'bg-green-100 text-green-800' : ($aiInsights['revenueTrend'] === 'decreasing' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                    {{ ucfirst($aiInsights['revenueTrend'] ?? 'stable') }} trend
                </span>
            </div>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Predicted Revenue (Next Month)</p>
                    <p class="text-2xl font-bold text-gray-900">KES {{ number_format($aiInsights['predictedNextMonthRevenue'], 2) }}</p>
                </div>
                <p class="text-xs text-gray-500">Based on average of last 3 months</p>
            </div>
        </x-card>
        @endif

        <!-- Recommendations -->
        @if(count($aiInsights['recommendations'] ?? []) > 0)
        <x-card class="dark:bg-[#252525] dark:border dark:border-[#333333] rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.3)]">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recommendations</h3>
            <div class="space-y-3">
                @foreach($aiInsights['recommendations'] as $recommendation)
                <div class="flex items-start gap-3 p-3 bg-blue-50 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <p class="text-sm text-gray-700 dark:text-gray-200 flex-1">{{ $recommendation }}</p>
                </div>
                @endforeach
            </div>
        </x-card>
        @endif
    </div>

    <!-- Risk Alerts from AI -->
    @if(count($aiInsights['riskAlerts'] ?? []) > 0)
    <div class="space-y-3">
        @foreach($aiInsights['riskAlerts'] as $alert)
        <x-alert :type="$alert['type']">
            {{ $alert['message'] }}
        </x-alert>
        @endforeach
    </div>
    @endif
    @endif

    <!-- Export Tools Section -->
    <x-card class="dark:bg-[#252525] dark:border dark:border-[#333333] rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.3)]">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Export Data</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300">Export your data for analysis or backup</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 border border-gray-200 rounded-lg hover:border-[#2B6EF6] transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-gray-900">Export Invoices</h4>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <p class="text-xs text-gray-500 mb-3">Download invoices as CSV or Excel</p>
                <div class="flex gap-2">
                    <a href="{{ route('user.data-export.invoices.csv') }}" class="inline-flex items-center px-4 py-2 text-sm min-h-[36px] font-medium text-gray-700 dark:text-gray-200 bg-gray-100 rounded hover:bg-gray-200 transition-colors">
                        CSV
                    </a>
                    <a href="{{ route('user.data-export.invoices.excel') }}" class="inline-flex items-center px-4 py-2 text-sm min-h-[36px] font-medium text-gray-700 dark:text-gray-200 bg-gray-100 rounded hover:bg-gray-200 transition-colors">
                        Excel
                    </a>
                </div>
            </div>
            <div class="p-4 border border-gray-200 rounded-lg hover:border-[#2B6EF6] transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-gray-900">Export Clients</h4>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <p class="text-xs text-gray-500 mb-3">Download client list as CSV or Excel</p>
                <div class="flex gap-2">
                    <a href="{{ route('user.data-export.clients.csv') }}" class="inline-flex items-center px-4 py-2 text-sm min-h-[36px] font-medium text-gray-700 dark:text-gray-200 bg-gray-100 rounded hover:bg-gray-200 transition-colors">
                        CSV
                    </a>
                    <a href="{{ route('user.data-export.clients.excel') }}" class="inline-flex items-center px-4 py-2 text-sm min-h-[36px] font-medium text-gray-700 dark:text-gray-200 bg-gray-100 rounded hover:bg-gray-200 transition-colors">
                        Excel
                    </a>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Alerts -->
    @if(($stats['overdueCount'] ?? 0) > 0)
    <x-alert type="warning">
        You have {{ $stats['overdueCount'] }} overdue invoice(s) totaling KES {{ number_format($stats['overdueAmount'] ?? 0, 2) }}.
        <a href="{{ route('user.invoices.index', ['status' => 'overdue']) }}" class="font-medium underline">View them</a>
    </x-alert>
    @endif

    @if(($stats['outstanding'] ?? 0) > 0)
    <x-alert type="info">
        You have KES {{ number_format($stats['outstanding'], 2) }} in outstanding invoices.
    </x-alert>
    @endif

    <!-- Inventory Alerts -->
    @if(isset($inventoryAlerts) && (($inventoryAlerts['out_of_stock_count'] ?? 0) > 0 || ($inventoryAlerts['low_stock_count'] ?? 0) > 0))
    <div class="space-y-4">
        @if(($inventoryAlerts['out_of_stock_count'] ?? 0) > 0)
        <div class="flex items-center justify-between p-4 bg-red-400/5 border border-red-400/10 rounded-2xl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-400/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-900 dark:text-[#F5F5F5]">Out of Stock Alert</p>
                    <p class="text-xs font-medium text-gray-500 dark:text-[#9A9A9A]">{{ $inventoryAlerts['out_of_stock_count'] }} critical items require restock</p>
                </div>
            </div>
            <a href="{{ route('user.inventory.index', ['status' => 'out_of_stock']) }}" class="text-sm font-bold text-red-600 dark:text-red-400 hover:underline">Manage Stock</a>
        </div>
        @endif

        @if(($inventoryAlerts['low_stock_count'] ?? 0) > 0)
        <div class="flex items-center justify-between p-4 bg-amber-400/5 border border-amber-400/10 rounded-2xl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-400/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-900 dark:text-[#F5F5F5]">Low Stock Warning</p>
                    <p class="text-xs font-medium text-gray-500 dark:text-[#9A9A9A]">{{ $inventoryAlerts['low_stock_count'] }} items below safety margin</p>
                </div>
            </div>
            <a href="{{ route('user.inventory.index', ['status' => 'low_stock']) }}" class="text-sm font-bold text-amber-600 dark:text-amber-400 hover:underline">Restock Planning</a>
        </div>
        @endif
    </div>
    @endif

    <!-- Inventory Alerts Detail Card -->
    @if(isset($inventoryAlerts) && (($inventoryAlerts['out_of_stock_count'] ?? 0) > 0 || ($inventoryAlerts['low_stock_count'] ?? 0) > 0))
    <x-card class="dark:bg-[#252525] dark:border dark:border-[#333333] rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.3)]">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Inventory Alerts</h2>
            <a href="{{ route('user.inventory.index') }}" class="text-sm text-[#2B6EF6] hover:text-[#2563EB] font-medium">View all inventory</a>
        </div>

        @if(($inventoryAlerts['out_of_stock_count'] ?? 0) > 0)
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-red-600 mb-3">Out of Stock ({{ $inventoryAlerts['out_of_stock_count'] }})</h3>
            <div class="space-y-2">
                @foreach(array_slice($inventoryAlerts['out_of_stock'] ?? [], 0, 5) as $item)
                <div class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ $item['name'] ?? 'Unknown' }}</p>
                        @if(isset($item['sku']) && $item['sku'])
                        <p class="text-xs text-gray-500">SKU: {{ $item['sku'] }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-red-600">0 in stock</p>
                        <a href="{{ route('user.inventory.show', $item['id']) }}" class="text-xs text-[#2B6EF6] hover:text-[#2563EB]">View</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(($inventoryAlerts['low_stock_count'] ?? 0) > 0)
        <div>
            <h3 class="text-sm font-semibold text-orange-600 mb-3">Low Stock ({{ $inventoryAlerts['low_stock_count'] }})</h3>
            <div class="space-y-2">
                @foreach(array_slice($inventoryAlerts['low_stock'] ?? [], 0, 5) as $item)
                <div class="flex items-center justify-between p-3 bg-orange-50 border border-orange-200 rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ $item['name'] ?? 'Unknown' }}</p>
                        @if(isset($item['sku']) && $item['sku'])
                        <p class="text-xs text-gray-500">SKU: {{ $item['sku'] }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-orange-600">
                            {{ number_format($item['current_stock'] ?? 0, 0) }} / {{ number_format($item['minimum_stock'] ?? 0, 0) }}
                        </p>
                        <a href="{{ route('user.inventory.show', $item['id']) }}" class="text-xs text-[#2B6EF6] hover:text-[#2563EB]">View</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </x-card>
    @endif

    <!-- Recent Activity Feed -->
    <x-card padding="none">
        <div class="px-8 py-6 border-b border-gray-100 dark:border-[#2A2A2A] flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-[#F5F5F5]">Recent Activity</h2>
                <p class="text-sm text-gray-500 dark:text-[#9A9A9A] mt-1">Real-time log of business transactions and operations</p>
            </div>
            <div class="hidden sm:flex items-center gap-4">
                <a href="{{ route('user.invoices.index') }}" class="text-sm font-bold text-[#2B6EF6] hover:text-[#2563EB] transition-colors">View All Invoices</a>
                @if(isset($additionalMetrics) && ($additionalMetrics['totalEstimates'] ?? 0) > 0)
                <a href="{{ route('user.estimates.index') }}" class="text-sm font-bold text-[#2B6EF6] hover:text-[#2563EB] transition-colors">Estimates Panel</a>
                @endif
            </div>
        </div>

        @if(isset($recentActivity) && count($recentActivity) > 0)
        <div class="divide-y divide-gray-200">
            @foreach($recentActivity as $activity)
            <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4 flex-1">
                        <!-- Activity Icon -->
                        <div class="flex-shrink-0 mt-1">
                            @if($activity['type'] === 'invoice')
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            @elseif($activity['type'] === 'payment')
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            @elseif($activity['type'] === 'estimate')
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            @endif
                        </div>

                        <!-- Activity Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                @if($activity['url'])
                                <a href="{{ $activity['url'] }}" class="text-sm font-bold text-gray-900 dark:text-[#F5F5F5] hover:text-[#2B6EF6] transition-colors">
                                    {{ $activity['title'] }}
                                </a>
                                @else
                                <span class="text-sm font-bold text-gray-900 dark:text-[#F5F5F5]">{{ $activity['title'] }}</span>
                                @endif
                                @if(isset($activity['status']))
                                @php
                                $statusService = new \App\Http\Services\InvoiceStatusService();
                                $statusVariant = $statusService::getStatusVariant($activity['status']);
                                @endphp
                                <span class="badge-{{ $statusVariant }} text-[10px]">{{ ucfirst($activity['status']) }}</span>
                                @endif
                            </div>
                            <p class="text-xs font-medium text-gray-500 dark:text-[#9A9A9A] mt-0.5">{{ $activity['description'] }}</p>
                            <p class="text-[10px] font-bold text-gray-300 dark:text-[#444444] uppercase tracking-widest mt-1">{{ \Carbon\Carbon::parse($activity['date'])->diffForHumans() }}</p>
                        </div>
                    </div>

                    <!-- Amount -->
                    @if(isset($activity['amount']) && $activity['amount'] > 0)
                    <div class="ml-4 flex-shrink-0">
                        <p class="text-sm font-semibold text-gray-900">KES {{ number_format($activity['amount'], 2) }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No recent activity</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Activity will appear here as you use the system.</p>
            <div class="mt-6">
                <a href="{{ route('user.invoices.create') }}">
                    <x-button variant="primary">Create Invoice</x-button>
                </a>
            </div>
        </div>
        @endif
    </x-card>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    (function() {
        // Premium Chart Defaults for 2026 SaaS Aesthetic
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9A9A9A' : '#6B7280';
        const borderColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';

        Chart.defaults.color = textColor;
        Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";
        Chart.defaults.font.size = 11;
        Chart.defaults.font.weight = '500';
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.padding = 20;
        Chart.defaults.scale.grid.color = borderColor;
        Chart.defaults.scale.grid.drawBorder = false;

        // Store chart instances to prevent re-initialization
        let revenueChartInstance = null;
        let statusChartInstance = null;
        let topClientsChartInstance = null;
        let paymentMethodChartInstance = null;
        let cashFlowForecastChartInstance = null;

        function initCharts() {
            // Revenue Chart
            const ctx = document.getElementById('revenueChart');
            if (ctx && !revenueChartInstance) {
                try {
                    const revenueData = @json($insights['revenue_trends'] ?? []);
                    if (revenueData && revenueData.length > 0) {
                        revenueChartInstance = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: revenueData.map(item => item.month),
                                datasets: [{
                                    label: 'Revenue (KES)',
                                    data: revenueData.map(item => item.revenue),
                                    borderColor: 'rgb(43, 110, 246)',
                                    backgroundColor: 'rgba(43, 110, 246, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                aspectRatio: 2,
                                animation: {
                                    duration: 1000,
                                    easing: 'easeInOutQuart'
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return 'KES ' + value.toLocaleString();
                                            }
                                        }
                                    }
                                },
                                layout: {
                                    padding: {
                                        top: 10,
                                        bottom: 10,
                                        left: 10,
                                        right: 10
                                    }
                                }
                            }
                        });
                    }
                } catch (error) {
                    console.error('Error initializing revenue chart:', error);
                }
            }

            // Status Distribution Doughnut Chart
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx && !statusChartInstance) {
                try {
                    const statusData = @json($statusDistribution ?? []);

                    if (!statusData || statusData.length === 0) {
                        console.warn('No status distribution data available');
                        if (statusCtx.parentElement) {
                            statusCtx.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500"><div class="text-center"><p class="text-sm">No invoice data available</p><p class="text-xs mt-1 text-gray-400">Create an invoice to see status distribution</p></div></div>';
                        }
                        return;
                    }

                    // Filter out statuses with count 0 and sort by count (descending) for consistent display
                    const filteredData = statusData
                        .filter(item => item && item.count > 0)
                        .sort((a, b) => (b.count || 0) - (a.count || 0));

                    if (filteredData.length === 0) {
                        console.warn('No invoices with status data to display');
                        if (statusCtx.parentElement) {
                            statusCtx.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500"><div class="text-center"><p class="text-sm">No invoices found</p><p class="text-xs mt-1 text-gray-400">Create an invoice to see status distribution</p></div></div>';
                        }
                        return;
                    }

                    // Ensure all required fields are present
                    const chartData = {
                        labels: filteredData.map(item => item.name || 'Unknown'),
                        datasets: [{
                            data: filteredData.map(item => item.count || 0),
                            backgroundColor: filteredData.map(item => item.bgColor || '#9CA3AF'),
                            borderColor: filteredData.map(item => item.color || '#6B7280'),
                            borderWidth: 3,
                            hoverBorderWidth: 5,
                            hoverOffset: 4,
                        }]
                    };

                    statusChartInstance = new Chart(statusCtx, {
                        type: 'doughnut',
                        data: chartData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: {
                                animateRotate: true,
                                animateScale: true,
                                duration: 1000,
                                easing: 'easeInOutQuart'
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    enabled: true,
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.parsed || 0;
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            if (total === 0) return label + ': ' + value;
                                            const percentage = ((value / total) * 100).toFixed(1);
                                            return label + ': ' + value + ' invoice' + (value !== 1 ? 's' : '') + ' (' + percentage + '%)';
                                        }
                                    },
                                    padding: 12,
                                    backgroundColor: 'rgba(0, 0, 0, 0.85)',
                                    cornerRadius: 6,
                                    displayColors: true,
                                    titleColor: '#ffffff',
                                    bodyColor: '#ffffff',
                                    borderColor: 'rgba(255, 255, 255, 0.1)',
                                    borderWidth: 1
                                }
                            },
                            cutout: '60%',
                            interaction: {
                                intersect: true,
                                mode: 'point'
                            }
                        }
                    });

                    console.log('Status chart initialized successfully with', filteredData.length, 'statuses');
                } catch (error) {
                    console.error('Error initializing status chart:', error);
                    console.error('Error details:', error.message, error.stack);
                    // Show error message in the chart container
                    if (statusCtx && statusCtx.parentElement) {
                        statusCtx.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-red-500"><div class="text-center"><p class="text-sm">Error loading chart</p><p class="text-xs mt-1 text-gray-400">Please refresh the page</p></div></div>';
                    }
                }
            } else if (!statusCtx) {
                console.warn('Status chart canvas element not found');
            }

            // Top Clients Bar Chart
            const topClientsCtx = document.getElementById('topClientsChart');
            if (topClientsCtx && !topClientsChartInstance) {
                try {
                    const topClientsData = @json($insights['top_clients'] ?? []);
                    if (topClientsData.length > 0) {
                        topClientsChartInstance = new Chart(topClientsCtx, {
                            type: 'bar',
                            data: {
                                labels: topClientsData.slice(0, 10).map(item => item.client_name),
                                datasets: [{
                                    label: 'Revenue (KES)',
                                    data: topClientsData.slice(0, 10).map(item => item.revenue),
                                    backgroundColor: 'rgba(43, 110, 246, 0.8)',
                                    borderColor: 'rgb(43, 110, 246)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                aspectRatio: 2,
                                animation: {
                                    duration: 1000,
                                    easing: 'easeInOutQuart'
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return 'KES ' + context.parsed.y.toLocaleString();
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return 'KES ' + value.toLocaleString();
                                            }
                                        }
                                    },
                                    x: {
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 0
                                        }
                                    }
                                },
                                layout: {
                                    padding: {
                                        top: 10,
                                        bottom: 10,
                                        left: 10,
                                        right: 10
                                    }
                                }
                            }
                        });
                    }
                } catch (error) {
                    console.error('Error initializing top clients chart:', error);
                }
            }

            // Payment Method Breakdown Donut Chart
            const paymentMethodCtx = document.getElementById('paymentMethodChart');
            if (paymentMethodCtx && !paymentMethodChartInstance) {
                try {
                    const paymentMethodData = @json($paymentMethodBreakdown['breakdown'] ?? []);
                    if (paymentMethodData.length > 0) {
                        const colors = ['#2B6EF6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'];
                        paymentMethodChartInstance = new Chart(paymentMethodCtx, {
                            type: 'doughnut',
                            data: {
                                labels: paymentMethodData.map(item => item.method),
                                datasets: [{
                                    data: paymentMethodData.map(item => item.total_amount),
                                    backgroundColor: paymentMethodData.map((item, index) => colors[index % colors.length]),
                                    borderWidth: 2,
                                    borderColor: '#ffffff'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                aspectRatio: 1,
                                animation: {
                                    duration: 1000,
                                    easing: 'easeInOutQuart'
                                },
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = context.parsed || 0;
                                                const percentage = paymentMethodData[context.dataIndex]?.percentage || 0;
                                                return label + ': KES ' + value.toLocaleString() + ' (' + percentage + '%)';
                                            }
                                        }
                                    }
                                },
                                layout: {
                                    padding: {
                                        top: 10,
                                        bottom: 10,
                                        left: 10,
                                        right: 10
                                    }
                                }
                            }
                        });
                    }
                } catch (error) {
                    console.error('Error initializing payment method chart:', error);
                }
            }

            // Cash Flow Forecast Line Chart
            const cashFlowForecastCtx = document.getElementById('cashFlowForecastChart');
            if (cashFlowForecastCtx && !cashFlowForecastChartInstance) {
                try {
                    const forecastData = @json($cashFlowForecast ?? []);
                    if (forecastData.length > 0) {
                        cashFlowForecastChartInstance = new Chart(cashFlowForecastCtx, {
                            type: 'line',
                            data: {
                                labels: forecastData.map(item => item.month),
                                datasets: [{
                                        label: 'Projected Inflow',
                                        data: forecastData.map(item => item.projected_inflow),
                                        borderColor: 'rgb(16, 185, 129)',
                                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                        tension: 0.4,
                                        fill: false
                                    },
                                    {
                                        label: 'Projected Outflow',
                                        data: forecastData.map(item => item.projected_outflow),
                                        borderColor: 'rgb(239, 68, 68)',
                                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                        tension: 0.4,
                                        fill: false
                                    },
                                    {
                                        label: 'Projected Net',
                                        data: forecastData.map(item => item.projected_net),
                                        borderColor: 'rgb(43, 110, 246)',
                                        backgroundColor: 'rgba(43, 110, 246, 0.1)',
                                        tension: 0.4,
                                        fill: true,
                                        borderDash: [5, 5]
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                aspectRatio: 2,
                                animation: {
                                    duration: 1000,
                                    easing: 'easeInOutQuart'
                                },
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return context.dataset.label + ': KES ' + context.parsed.y.toLocaleString();
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: false,
                                        ticks: {
                                            callback: function(value) {
                                                return 'KES ' + value.toLocaleString();
                                            }
                                        }
                                    }
                                },
                                layout: {
                                    padding: {
                                        top: 10,
                                        bottom: 10,
                                        left: 10,
                                        right: 10
                                    }
                                }
                            }
                        });
                    }
                } catch (error) {
                    console.error('Error initializing cash flow forecast chart:', error);
                }
            }
        }

        // Initialize chart s when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCharts);
        } else {
            initCharts();
        }
    })();
</script>




<script>
    // Dashboard Tour
    (function() {
        const onboardingCompleted = {
            {
                auth() - > user() - > onboarding_completed ? 'true' : 'false'
            }
        };
        const tourSeen = localStorage.getItem('dashboard-tour-seen');

        if (onboardingCompleted && tourSeen !== 'true') {
            // Simple inline tour implementation
            setTimeout(() => {
                const steps = [{
                        target: '#company-switcher',
                        title: 'Company Switcher',
                        content: 'Switch between your companies using this selector. Press ⌘K (Mac) or Ctrl+K (Windows) for quick access.',
                    },
                    {
                        target: '#dashboard-new-invoice-btn',
                        title: 'Create Invoice',
                        content: 'Click here to create your first invoice. You can add clients, items, and send invoices directly from here.',
                    }
                ];

                let currentStep = 0;
                let overlay = null;
                let tooltip = null;

                function showStep(index) {
                    if (index < 0 || index >= steps.length) {
                        closeTour();
                        return;
                    }

                    currentStep = index;
                    const step = steps[index];
                    const target = document.querySelector(step.target);
                    if (!target) {
                        closeTour();
                        return;
                    }

                    // Create overlay
                    if (!overlay) {
                        overlay = document.createElement('div');
                        overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9998;pointer-events:none;';
                        document.body.appendChild(overlay);
                    }

                    // Create tooltip
                    if (tooltip) tooltip.remove();
                    tooltip = document.createElement('div');
                    tooltip.style.cssText = 'position:fixed;z-index:9999;background:white;border-radius:8px;padding:20px;max-width:320px;box-shadow:0 10px 25px rgba(0,0,0,0.2);';

                    const rect = target.getBoundingClientRect();
                    tooltip.style.top = (rect.bottom + 10) + 'px';
                    tooltip.style.left = Math.max(10, rect.left + (rect.width / 2) - 160) + 'px';

                    tooltip.innerHTML = `
                        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:12px;">
                            <h3 style="font-size:18px;font-weight:600;color:#111827;">${step.title}</h3>
                            <button onclick="window.closeTour()" style="color:#9ca3af;cursor:pointer;">✕</button>
                        </div>
                        <p style="font-size:14px;color:#4b5563;margin-bottom:16px;">${step.content}</p>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <div style="display:flex;gap:8px;">
                                ${steps.map((_, i) => `<div style="width:8px;height:8px;border-radius:50%;background:${i===currentStep?'#2B6EF6':'#d1d5db'};"></div>`).join('')}
                            </div>
                            <div style="display:flex;gap:8px;">
                                ${currentStep > 0 ? `<button onclick="window.tourPrev()" style="padding:6px 12px;font-size:14px;color:#374151;background:#f3f4f6;border-radius:6px;border:none;cursor:pointer;">Previous</button>` : ''}
                                ${currentStep < steps.length - 1 ? `<button onclick="window.tourNext()" style="padding:6px 12px;font-size:14px;color:white;background:#2B6EF6;border-radius:6px;border:none;cursor:pointer;">Next</button>` : `<button onclick="window.closeTour()" style="padding:6px 12px;font-size:14px;color:white;background:#2B6EF6;border-radius:6px;border:none;cursor:pointer;">Got it!</button>`}
                            </div>
                        </div>
                    `;

                    document.body.appendChild(tooltip);

                    // Highlight target
                    target.style.position = 'relative';
                    target.style.zIndex = '10000';
                    target.style.transition = 'all 0.3s';
                    target.style.transform = 'scale(1.02)';
                    target.style.boxShadow = '0 0 0 4px rgba(43, 110, 246, 0.3)';
                }

                window.tourNext = function() {
                    const target = document.querySelector(steps[currentStep].target);
                    if (target) {
                        target.style.transform = '';
                        target.style.boxShadow = '';
                    }
                    showStep(currentStep + 1);
                };

                window.tourPrev = function() {
                    const target = document.querySelector(steps[currentStep].target);
                    if (target) {
                        target.style.transform = '';
                        target.style.boxShadow = '';
                    }
                    showStep(currentStep - 1);
                };

                window.closeTour = function() {
                    if (overlay) overlay.remove();
                    if (tooltip) tooltip.remove();
                    steps.forEach(s => {
                        const t = document.querySelector(s.target);
                        if (t) {
                            t.style.transform = '';
                            t.style.boxShadow = '';
                        }
                    });
                    localStorage.setItem('dashboard-tour-seen', 'true');
                };

                showStep(0);
            }, 1000);
        }
    })();
</script>
@endpush
<!-- Feedback Form Component -->
<x-feedback-form class="dark:text-gray-100" />
@endsection