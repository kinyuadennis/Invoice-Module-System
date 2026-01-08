@extends('layouts.user')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Dashboard</h1>
            <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="flex items-center gap-2 px-3 py-1.5 bg-blue-500/5 border border-blue-500/10 rounded-full">
                <div class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></div>
                <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest px-1">Authority Active</span>
            </span>
            <div class="hidden lg:flex items-center gap-2 ml-4">
                <a href="{{ route('user.invoices.create') }}" class="inline-flex items-center px-4 py-2 text-xs font-black text-white bg-blue-500 rounded-xl hover:bg-blue-600 shadow-lg shadow-blue-500/20 transition-all uppercase tracking-widest">
                    New Invoice
                </a>
            </div>
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

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Revenue -->
        <x-card padding="sm" class="interactive-card bg-gradient-to-br from-blue-600 to-indigo-700 !border-none overflow-hidden relative group cursor-pointer" onclick="window.location.href='{{ route('user.invoices.index', ['status' => 'paid']) }}'">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-white/20 transition-all"></div>
            <div class="flex items-center relative z-10">
                <div class="flex-shrink-0 bg-white/20 p-3 rounded-xl backdrop-blur-md">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1 text-white">
                    <dl>
                        <dt class="text-[10px] font-black opacity-70 uppercase tracking-widest mb-1">Total Revenue</dt>
                        <dd class="text-lg font-black tracking-tight leading-none whitespace-nowrap">KES {{ number_format($stats['totalRevenue'] ?? 0, 0) }}</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <!-- Outstanding -->
        <x-card padding="sm" class="interactive-card cursor-pointer" onclick="window.location.href='{{ route('user.invoices.index', ['status' => 'sent']) }}'">
            <div class="flex items-center">
                <div class="flex-shrink-0 icon-bg-amber p-3 rounded-xl">
                    <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Outstanding</dt>
                        <dd class="text-2xl font-black text-gray-900 dark:text-[#F5F5F5] tracking-tight">KES {{ number_format($stats['outstanding'] ?? 0, 0) }}</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <!-- Overdue -->
        <x-card padding="sm" class="interactive-card cursor-pointer" onclick="window.location.href='{{ route('user.invoices.index', ['status' => 'overdue']) }}'">
            <div class="flex items-center">
                <div class="flex-shrink-0 icon-bg-red p-3 rounded-xl">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Overdue Count</dt>
                        <dd class="text-2xl font-black text-red-600 dark:text-red-400 tracking-tight">{{ $stats['overdueCount'] ?? 0 }} <span class="text-xs font-bold text-gray-400">Items</span></dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <!-- Paid Invoices -->
        <x-card padding="sm" class="interactive-card cursor-pointer" onclick="window.location.href='{{ route('user.invoices.index', ['status' => 'paid']) }}'">
            <div class="flex items-center">
                <div class="flex-shrink-0 icon-bg-emerald p-3 rounded-xl">
                    <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Paid Invoices</dt>
                        <dd class="text-2xl font-black text-gray-900 dark:text-[#F5F5F5] tracking-tight">{{ $stats['paidCount'] ?? 0 }} <span class="text-xs font-bold text-gray-400">Total</span></dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <!-- Secondary Metrics Row -->
        <div class="lg:col-span-4 grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Expenses -->
            <x-card padding="sm" class="interactive-card cursor-pointer" onclick="window.location.href='{{ route('user.expenses.index') }}'">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Total Expenses</p>
                        <p class="text-xl font-black text-gray-900 dark:text-[#F5F5F5]">KES {{ number_format($expenseStats['total_expenses'] ?? 0, 0) }}</p>
                    </div>
                </div>
            </x-card>

            <!-- Net Cash Flow -->
            <x-card padding="sm" class="interactive-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Net Cash Flow</p>
                        <p class="text-xl font-black {{ ($cashFlow['net_cash_flow'] ?? 0) >= 0 ? 'text-emerald-500' : 'text-red-500' }}">KES {{ number_format($cashFlow['net_cash_flow'] ?? 0, 0) }}</p>
                    </div>
                    @if(isset($cashFlow['cash_flow_change']) && $cashFlow['cash_flow_change'] != 0)
                    <div class="px-2 py-1 rounded-lg {{ $cashFlow['cash_flow_change'] >= 0 ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-500' }} text-[10px] font-black">
                        {{ $cashFlow['cash_flow_change'] >= 0 ? '↑' : '↓' }} {{ abs(number_format($cashFlow['cash_flow_change'], 1)) }}%
                    </div>
                    @endif
                </div>
            </x-card>

            <!-- Avg Invoice -->
            <x-card padding="sm" class="interactive-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Avg Invoice</p>
                        <p class="text-xl font-black text-gray-900 dark:text-[#F5F5F5]">KES {{ number_format($stats['averageInvoiceValue'] ?? 0, 0) }}</p>
                    </div>
                    <div class="icon-bg-indigo p-2 rounded-lg">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </x-card>

            <!-- Success Rate -->
            <x-card padding="sm" class="interactive-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Success Rate</p>
                        <p class="text-xl font-black text-gray-900 dark:text-[#F5F5F5]">{{ number_format($additionalMetrics['paymentSuccessRate'] ?? 0, 1) }}%</p>
                    </div>
                    <div class="w-8 h-8 rounded-full border-2 border-emerald-500/20 flex items-center justify-center">
                        <div class="w-1 h-1 rounded-full bg-emerald-500"></div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Analytical Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Revenue Analytics -->
        @if(isset($insights['revenue_trends']) && count($insights['revenue_trends']) > 0)
        <x-card>
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Revenue Analytics</h2>
                    <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">12-month performance trend</p>
                </div>
                <div class="icon-bg-blue p-2 rounded-lg">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <div class="h-[300px] relative">
                <canvas id="revenueChart"
                    data-labels="{{ json_encode(array_column($insights['revenue_trends'], 'month')) }}"
                    data-values="{{ json_encode(array_column($insights['revenue_trends'], 'revenue')) }}"></canvas>
            </div>
        </x-card>
        @endif

        <!-- Status Distribution -->
        @php
        $hasStatusData = isset($statusDistribution) && count($statusDistribution) > 0 && collect($statusDistribution)->sum('count') > 0;
        @endphp
        @if($hasStatusData)
        <x-card>
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Status Distribution</h2>
                    <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Current invoicing health</p>
                </div>
                <div class="icon-bg-purple p-2 rounded-lg">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                </div>
            </div>
            <div class="h-[300px] relative">
                <canvas id="statusChart"
                    data-values="{{ json_encode(collect($statusDistribution)->map(fn($v) => ['name' => $v['name'], 'count' => $v['count'], 'color' => $v['color']])->values()) }}"></canvas>
            </div>
        </x-card>
        @endif
    </div>

    <!-- Duplicate Detection Alert -->
    @if(isset($complianceData) && ($complianceData['duplicateCount'] ?? 0) > 0)
    <x-alert type="warning">
        <div class="flex items-center justify-between">
            <div>
                <strong>Duplicate Detection:</strong> {{ $complianceData['duplicateCount'] }} potential duplicate invoice(s) detected.
            </div>
            <a href="{{ route('user.invoices.index') }}" class="ml-4 text-sm font-medium underline font-bold">Review Invoices</a>
        </div>
    </x-alert>
    @endif

    <!-- Specialized Metrics Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- eTIMS Compliance -->
        <x-card>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">eTIMS Compliance</h3>
                    <p class="text-[10px] font-bold text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Submission Health</p>
                </div>
                <div class="badge-{{ ($complianceData['etimsComplianceRate'] ?? 0) >= 95 ? 'emerald' : (($complianceData['etimsComplianceRate'] ?? 0) >= 80 ? 'amber' : 'red') }}">
                    {{ number_format($complianceData['etimsComplianceRate'] ?? 0, 1) }}%
                </div>
            </div>
            <div class="space-y-4">
                <div class="w-full bg-gray-100 dark:bg-[#2A2A2A] rounded-full h-1.5 overflow-hidden">
                    <div class="{{ ($complianceData['etimsComplianceRate'] ?? 0) >= 95 ? 'bg-emerald-500' : (($complianceData['etimsComplianceRate'] ?? 0) >= 80 ? 'bg-amber-500' : 'bg-red-500') }} h-full rounded-full transition-all duration-1000" style="width: {{ (int)($complianceData['etimsComplianceRate'] ?? 0) }}%;"></div>
                </div>
                <div class="flex justify-between items-end">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Submitted</span>
                        <span class="text-xl font-black text-gray-900 dark:text-[#F5F5F5]">{{ $complianceData['etimsSubmittedCount'] ?? 0 }}</span>
                    </div>
                    <div class="flex flex-col text-right">
                        <span class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Target</span>
                        <span class="text-xl font-black text-gray-900 dark:text-[#F5F5F5]">{{ $complianceData['totalInvoicesForCompliance'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Risk Assessment -->
        <x-card>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Risk Assessment</h3>
                    <p class="text-[10px] font-bold text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Fraud Indicators</p>
                </div>
                <div class="icon-bg-red p-2 rounded-lg">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 dark:bg-[#111111] border border-gray-100 dark:border-[#2A2A2A] rounded-2xl">
                    <div class="text-2xl font-black {{ ($fraudIndicators['flagged_count'] ?? 0) > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-[#F5F5F5]' }}">
                        {{ $fraudIndicators['flagged_count'] ?? 0 }}
                    </div>
                    <div class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mt-1">Flagged</div>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-[#111111] border border-gray-100 dark:border-[#2A2A2A] rounded-2xl">
                    <div class="text-2xl font-black text-gray-900 dark:text-[#F5F5F5]">{{ number_format($fraudIndicators['average_fraud_score'] ?? 0, 1) }}</div>
                    <div class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mt-1">Avg Score</div>
                </div>
            </div>
        </x-card>

        <!-- DSO Analytics -->
        <x-card>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">DSO Analytics</h3>
                    <p class="text-[10px] font-bold text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Days Sales Outstanding</p>
                </div>
                <div class="icon-bg-amber p-2 rounded-lg">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-4xl font-black text-gray-900 dark:text-[#F5F5F5] tracking-tighter">{{ number_format($insights['dso'] ?? 0, 1) }}</p>
                    <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Avg Days to Pay</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-black {{ ($insights['dso_change'] ?? 0) <= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                        {{ ($insights['dso_change'] ?? 0) <= 0 ? '↓' : '↑' }} {{ abs(number_format($insights['dso_change'] ?? 0, 1)) }}%
                    </p>
                    <p class="text-[10px] font-bold text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">vs prev period</p>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Activity & Analytics Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Activity Feed -->
        <x-card padding="none">
            <div class="px-8 py-6 border-b border-gray-100 dark:border-[#2A2A2A] flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Recent Activity</h2>
                    <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Direct transaction log</p>
                </div>
                <a href="{{ route('user.invoices.index') }}" class="text-xs font-black text-blue-500 hover:text-blue-600 uppercase tracking-widest">View All</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-[#2A2A2A]">
                @forelse($recentActivity ?? [] as $activity)
                <div class="px-8 py-5 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-white/5 flex items-center justify-center">
                                @if($activity['type'] === 'invoice')
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                @elseif($activity['type'] === 'payment')
                                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                @else
                                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-black text-gray-900 dark:text-white">{{ $activity['title'] }}</p>
                                <p class="text-xs font-medium text-gray-500 dark:text-[#9A9A9A]">{{ $activity['description'] }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-black text-gray-900 dark:text-white">KES {{ number_format($activity['amount'] ?? 0, 0) }}</p>
                            <p class="text-[10px] font-bold text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">{{ \Carbon\Carbon::parse($activity['date'])->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-8 py-12 text-center text-gray-500">No recent activity found.</div>
                @endforelse
            </div>
        </x-card>

        <!-- Aging & Top Clients -->
        <div class="space-y-8">
            <!-- Invoice Aging Report -->
            <x-card>
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Aging Report</h2>
                        <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Receivables by age group</p>
                    </div>
                    <div class="icon-bg-amber p-2 rounded-lg">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="space-y-4">
                    @foreach($insights['invoice_aging'] ?? [] as $ageGroup => $data)
                    @if($data['count'] > 0)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-white/[0.02] border border-gray-100 dark:border-[#2A2A2A] rounded-2xl">
                        <div>
                            <p class="text-sm font-black text-gray-900 dark:text-white">{{ $ageGroup }} Days</p>
                            <p class="text-[10px] font-bold text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">{{ $data['count'] }} Open Invoices</p>
                        </div>
                        <p class="text-lg font-black text-gray-900 dark:text-white tracking-tight">KES {{ number_format($data['amount'], 0) }}</p>
                    </div>
                    @endif
                    @endforeach
                </div>
            </x-card>

            <!-- Top Clients -->
            <x-card>
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Top Performance</h2>
                        <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Highest revenue contributors</p>
                    </div>
                </div>
                <div class="h-[200px] relative">
                    <canvas id="topClientsChart"
                        data-labels="{{ json_encode(collect($insights['top_clients'] ?? [])->slice(0, 5)->pluck('client_name')) }}"
                        data-values="{{ json_encode(collect($insights['top_clients'] ?? [])->slice(0, 5)->pluck('revenue')) }}"></canvas>
                </div>
            </x-card>
        </div>
    </div>


    <!-- Additional Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Payment Method Breakdown -->
        @if(isset($paymentMethodBreakdown) && count($paymentMethodBreakdown['breakdown'] ?? []) > 0)
        <x-card>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Payment Methods</h2>
                <p class="text-[10px] font-bold text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Revenue Distribution</p>
            </div>
            <div class="h-[300px] relative">
                <canvas id="paymentMethodChart"
                    data-values="{{ json_encode(collect($paymentMethodBreakdown['breakdown'])->map(fn($item) => ['method' => $item['method'], 'amount' => $item['total_amount'], 'percentage' => $item['percentage']])) }}"></canvas>
            </div>
        </x-card>
        @endif

        <!-- Cash Flow Forecast -->
        @if(isset($cashFlowForecast) && count($cashFlowForecast) > 0)
        <x-card>
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Cash Flow Forecast</h2>
                    <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Next 3 months projection</p>
                </div>
            </div>
            <div class="h-[300px] relative">
                <canvas id="cashFlowForecastChart"
                    data-labels="{{ json_encode(collect($cashFlowForecast)->pluck('month')) }}"
                    data-values="{{ json_encode(collect($cashFlowForecast)->map(fn($item) => ['inflow' => $item['projected_inflow'], 'outflow' => $item['projected_outflow'], 'net' => $item['projected_net']])) }}"></canvas>
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
    <x-card class="interactive-card">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Export Authority</h3>
                <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Direct data extraction tools</p>
            </div>
            <div class="icon-bg-blue p-2 rounded-lg">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-6 bg-gray-50 dark:bg-white/[0.02] border border-gray-100 dark:border-[#2A2A2A] rounded-2xl group hover:border-blue-500/30 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Invoice Ledger</h4>
                    <span class="text-[10px] font-bold text-gray-400 dark:text-[#9A9A9A] uppercase">Full History</span>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('user.data-export.invoices.csv') }}" class="flex-1 inline-flex items-center justify-center px-4 py-2.5 text-xs font-black text-gray-700 dark:text-gray-200 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl hover:bg-gray-50 dark:hover:bg-white/10 transition-all uppercase tracking-widest">
                        CSV Extract
                    </a>
                    <a href="{{ route('user.data-export.invoices.excel') }}" class="flex-1 inline-flex items-center justify-center px-4 py-2.5 text-xs font-black text-white bg-blue-600 rounded-xl hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/20 transition-all uppercase tracking-widest">
                        Excel Pro
                    </a>
                </div>
            </div>
            <div class="p-6 bg-gray-50 dark:bg-white/[0.02] border border-gray-100 dark:border-[#2A2A2A] rounded-2xl group hover:border-emerald-500/30 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Client Audit</h4>
                    <span class="text-[10px] font-bold text-gray-400 dark:text-[#9A9A9A] uppercase">Management</span>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('user.data-export.clients.csv') }}" class="flex-1 inline-flex items-center justify-center px-4 py-2.5 text-xs font-black text-gray-700 dark:text-gray-200 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl hover:bg-gray-50 dark:hover:bg-white/10 transition-all uppercase tracking-widest">
                        CSV Extract
                    </a>
                    <a href="{{ route('user.data-export.clients.excel') }}" class="flex-1 inline-flex items-center justify-center px-4 py-2.5 text-xs font-black text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-500/20 transition-all uppercase tracking-widest">
                        Excel Pro
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
            const revenueEl = document.getElementById('revenueChart');
            if (revenueEl && !revenueChartInstance) {
                try {
                    const labels = JSON.parse(revenueEl.getAttribute('data-labels'));
                    const values = JSON.parse(revenueEl.getAttribute('data-values'));

                    revenueChartInstance = new Chart(revenueEl, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Revenue (KES)',
                                data: values,
                                borderColor: 'rgb(43, 110, 246)',
                                backgroundColor: 'rgba(43, 110, 246, 0.1)',
                                tension: 0.4,
                                fill: true,
                                pointRadius: 4,
                                pointBackgroundColor: 'rgb(43, 110, 246)',
                                pointBorderWidth: 2,
                                pointBorderColor: '#fff',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0,0,0,0.8)',
                                    padding: 12,
                                    titleFont: {
                                        weight: '800'
                                    },
                                    callbacks: {
                                        label: (context) => `KES ${context.parsed.y.toLocaleString()}`
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: borderColor
                                    },
                                    ticks: {
                                        callback: (value) => value >= 1000 ? (value / 1000) + 'k' : value
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                } catch (error) {
                    console.error('Revenue Chart Error:', error);
                }
            }

            // Status Distribution Chart
            const statusEl = document.getElementById('statusChart');
            if (statusEl && !statusChartInstance) {
                try {
                    const data = JSON.parse(statusEl.getAttribute('data-values'));
                    statusChartInstance = new Chart(statusEl, {
                        type: 'doughnut',
                        data: {
                            labels: data.map(d => d.name),
                            datasets: [{
                                data: data.map(d => d.count),
                                backgroundColor: data.map(d => d.color),
                                borderWidth: 0,
                                hoverOffset: 10
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '75%',
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0,0,0,0.8)',
                                    padding: 12,
                                    callbacks: {
                                        label: (context) => `${context.label}: ${context.parsed} item(s)`
                                    }
                                }
                            }
                        }
                    });
                } catch (error) {
                    console.error('Status Chart Error:', error);
                }
            }

            // Top Clients Chart
            const clientsEl = document.getElementById('topClientsChart');
            if (clientsEl && !topClientsChartInstance) {
                try {
                    const labels = JSON.parse(clientsEl.getAttribute('data-labels'));
                    const values = JSON.parse(clientsEl.getAttribute('data-values'));
                    topClientsChartInstance = new Chart(clientsEl, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Revenue',
                                data: values,
                                backgroundColor: 'rgba(43, 110, 246, 0.8)',
                                borderRadius: 8,
                                barThickness: 20
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: borderColor
                                    },
                                    ticks: {
                                        display: false
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                } catch (error) {
                    console.error('Clients Chart Error:', error);
                }
            }
            // Payment Methods Chart
            const paymentEl = document.getElementById('paymentMethodChart');
            if (paymentEl && !paymentMethodChartInstance) {
                try {
                    const data = JSON.parse(paymentEl.getAttribute('data-values'));
                    const colors = ['#2B6EF6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'];
                    paymentMethodChartInstance = new Chart(paymentEl, {
                        type: 'doughnut',
                        data: {
                            labels: data.map(d => d.method),
                            datasets: [{
                                data: data.map(d => d.amount),
                                backgroundColor: colors,
                                borderWidth: 0,
                                hoverOffset: 12
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 10,
                                        padding: 15
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0,0,0,0.8)',
                                    padding: 12,
                                    callbacks: {
                                        label: (context) => {
                                            const item = data[context.dataIndex];
                                            return `${context.label}: KES ${item.amount.toLocaleString()} (${item.percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                } catch (error) {
                    console.error('Payment Chart Error:', error);
                }
            }

            // Cash Flow Forecast Chart
            const cashFlowEl = document.getElementById('cashFlowForecastChart');
            if (cashFlowEl && !cashFlowForecastChartInstance) {
                try {
                    const labels = JSON.parse(cashFlowEl.getAttribute('data-labels'));
                    const data = JSON.parse(cashFlowEl.getAttribute('data-values'));
                    cashFlowForecastChartInstance = new Chart(cashFlowEl, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                    label: 'Inflow',
                                    data: data.map(d => d.inflow),
                                    borderColor: '#10B981',
                                    tension: 0.4,
                                    fill: false
                                },
                                {
                                    label: 'Outflow',
                                    data: data.map(d => d.outflow),
                                    borderColor: '#EF4444',
                                    tension: 0.4,
                                    fill: false
                                },
                                {
                                    label: 'Net',
                                    data: data.map(d => d.net),
                                    borderColor: '#2B6EF6',
                                    backgroundColor: 'rgba(43, 110, 246, 0.1)',
                                    tension: 0.4,
                                    fill: true,
                                    borderDash: [5, 5]
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0,0,0,0.8)',
                                    padding: 12,
                                    callbacks: {
                                        label: (context) => `${context.dataset.label}: KES ${context.parsed.y.toLocaleString()}`
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    grid: {
                                        color: borderColor
                                    },
                                    ticks: {
                                        callback: (v) => v >= 1000 ? (v / 1000) + 'k' : v
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                } catch (error) {
                    console.error('Cash Flow Chart Error:', error);
                }
            }
        }

        // Initialize charts when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCharts);
        } else {
            initCharts();
        }
    })();
</script>
</div>

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