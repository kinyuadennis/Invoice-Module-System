@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Admin Dashboard</h1>
            <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Overview of all system activity</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="flex items-center gap-2 px-3 py-1.5 bg-blue-500/5 border border-blue-500/10 rounded-full">
                <div class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></div>
                <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest px-1">System Live</span>
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Companies -->
        <x-card padding="sm" class="interactive-card">
            <div class="flex items-center">
                <div class="flex-shrink-0 icon-bg-purple p-3 rounded-xl">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Total Entities</dt>
                        <dd class="text-2xl font-black text-gray-900 dark:text-[#F5F5F5] tracking-tight">{{ $stats['totalCompanies'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <!-- Total Users -->
        <x-card padding="sm" class="interactive-card">
            <div class="flex items-center">
                <div class="flex-shrink-0 icon-bg-indigo p-3 rounded-xl">
                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Total Users</dt>
                        <dd class="text-2xl font-black text-gray-900 dark:text-[#F5F5F5] tracking-tight">{{ $stats['totalUsers'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <!-- Total Invoices -->
        <x-card padding="sm" class="interactive-card">
            <div class="flex items-center">
                <div class="flex-shrink-0 icon-bg-blue p-3 rounded-xl">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Lifetime Invoices</dt>
                        <dd class="text-2xl font-black text-gray-900 dark:text-[#F5F5F5] tracking-tight">{{ $stats['totalInvoices'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <!-- Total Revenue -->
        <x-card padding="sm" class="interactive-card bg-gradient-to-br from-blue-600 to-indigo-700 !border-none overflow-hidden relative group">
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

        <!-- Secondary Metrics Row -->
        <div class="lg:col-span-4 grid grid-cols-1 md:grid-cols-3 gap-6">
            @if(isset($revenue))
            <x-card padding="sm" class="interactive-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">This Month</p>
                        <p class="text-xl font-black text-gray-900 dark:text-[#F5F5F5]">KES {{ number_format($revenue['thisMonth'] ?? 0, 0) }}</p>
                    </div>
                    @if(isset($revenue['monthOverMonth']))
                    <div class="px-2 py-1 rounded-lg {{ $revenue['monthOverMonth'] >= 0 ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-500' }} text-[10px] font-black">
                        {{ $revenue['monthOverMonth'] >= 0 ? '↑' : '↓' }} {{ abs(number_format($revenue['monthOverMonth'], 1)) }}%
                    </div>
                    @endif
                </div>
            </x-card>
            @endif

            <x-card padding="sm" class="interactive-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Overdue Volume</p>
                        <p class="text-xl font-black text-red-600 dark:text-red-400 tracking-tight">{{ $stats['overdueInvoices'] ?? 0 }} <span class="text-xs font-bold text-gray-400">Items</span></p>
                    </div>
                    <div class="icon-bg-red p-2 rounded-lg">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </x-card>

            <x-card padding="sm" class="interactive-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Pending Processing</p>
                        <p class="text-xl font-black text-amber-600 dark:text-amber-400 tracking-tight">{{ $stats['pendingInvoices'] ?? 0 }} <span class="text-xs font-bold text-gray-400">Items</span></p>
                    </div>
                    <div class="icon-bg-amber p-2 rounded-lg">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Monthly Revenue Trend -->
        @if(isset($monthlyTrends) && count($monthlyTrends) > 0)
        <x-card>
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Revenue Analytics</h2>
                    <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">System-wide performance trend</p>
                </div>
                <div class="icon-bg-blue p-2 rounded-lg">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <div class="h-[300px] relative">
                <canvas id="revenueChart"
                    data-labels="{{ json_encode(array_column($monthlyTrends, 'month')) }}"
                    data-values="{{ json_encode(array_column($monthlyTrends, 'revenue')) }}"></canvas>
            </div>
        </x-card>
        @endif

        <!-- Invoice Status Distribution -->
        @if(isset($invoiceStatusDistribution) && count($invoiceStatusDistribution) > 0)
        <x-card>
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Status Distribution</h2>
                    <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Current system health overview</p>
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
                    data-values="{{ json_encode($invoiceStatusDistribution) }}"></canvas>
            </div>
        </x-card>
        @endif
    </div>

    <!-- Top Companies -->
    @if(isset($topCompanies) && count($topCompanies) > 0)
    <x-card>
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Top Entities</h2>
                <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Highest revenue generators</p>
            </div>
            <a href="{{ route('admin.companies.index') }}" class="btn-ripple inline-flex items-center px-4 py-2 bg-gray-50 dark:bg-[#111111] border border-gray-200 dark:border-[#222222] rounded-xl text-sm font-bold text-gray-700 dark:text-[#D4D4D4] hover:bg-gray-100 transition-all">
                View All
            </a>
        </div>
        <div class="overflow-x-auto -mx-6 px-6">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="text-left font-black uppercase tracking-widest text-[10px] text-gray-400 dark:text-[#9A9A9A]">Company</th>
                        <th class="text-right font-black uppercase tracking-widest text-[10px] text-gray-400 dark:text-[#9A9A9A]">Volume</th>
                        <th class="text-right font-black uppercase tracking-widest text-[10px] text-gray-400 dark:text-[#9A9A9A]">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topCompanies as $company)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-[#111111] flex items-center justify-center text-[10px] font-black text-blue-400 border border-white/5">
                                    {{ substr($company['name'], 0, 1) }}
                                </div>
                                <a href="{{ route('admin.companies.show', $company['id']) }}" class="text-sm font-bold text-gray-900 dark:text-white hover:text-blue-500">{{ $company['name'] }}</a>
                            </div>
                        </td>
                        <td class="text-right text-xs font-medium text-gray-500">{{ $company['invoices_count'] ?? 0 }} invoices</td>
                        <td class="text-right font-mono font-bold text-gray-900 dark:text-white">KES {{ number_format($company['revenue'] ?? 0, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
    @endif

    <!-- Recent Companies -->
    @if(isset($recentCompanies) && $recentCompanies->count() > 0)
    <x-card>
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Recent Registrations</h2>
                <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Newest entities on the platform</p>
            </div>
            <div class="icon-bg-emerald p-2 rounded-lg text-emerald-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </div>
        </div>
        <div class="space-y-4">
            @foreach($recentCompanies as $company)
            <div class="group flex items-center justify-between p-4 bg-gray-50 dark:bg-[#111111]/50 border border-gray-100 dark:border-[#222222] rounded-2xl hover:border-blue-500/30 transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 font-black">
                        {{ strtoupper(substr($company['name'], 0, 1)) }}
                    </div>
                    <div>
                        <a href="{{ route('admin.companies.show', $company['id']) }}" class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-blue-500 transition-colors">{{ $company['name'] }}</a>
                        <p class="text-xs font-medium text-gray-500">{{ $company['owner'] }} • {{ $company['created_at']->format('M d, Y') }}</p>
                    </div>
                </div>
                <span class="badge-emerald text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full">New</span>
            </div>
            @endforeach
        </div>
    </x-card>
    @endif

    <!-- Recent Invoices -->
    <x-card padding="none">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-[#333333] flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Recent Invoices</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300">Latest invoices across all users</p>
            </div>
            <a href="{{ route('admin.invoices.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">View all</a>
        </div>

        @if(isset($recentInvoices) && count($recentInvoices) > 0)
        <x-table>
            <x-slot name="header">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                </tr>
            </x-slot>
            @foreach($recentInvoices as $invoice)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <a href="{{ route('admin.invoices.show', $invoice['id']) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                        {{ $invoice['invoice_reference'] ?? $invoice['invoice_number'] }}
                    </a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $invoice['client']['name'] ?? 'Unknown' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                    @if($invoice['company'])
                    <a href="{{ route('admin.companies.show', $invoice['company']['id']) }}" class="text-indigo-600 hover:text-indigo-900">
                        {{ $invoice['company']['name'] }}
                    </a>
                    @else
                    <span class="text-gray-400">No Company</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                    {{ $invoice['user']['name'] ?? 'Unknown' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @php
                    $statusVariant = match(strtolower($invoice['status'] ?? 'draft')) {
                    'paid' => 'success',
                    'sent' => 'info',
                    'overdue' => 'danger',
                    'pending' => 'warning',
                    default => 'default'
                    };
                    @endphp
                    <x-badge :variant="$statusVariant">{{ ucfirst($invoice['status'] ?? 'draft') }}</x-badge>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                    KES {{ number_format($invoice['total'] ?? 0, 2) }}
                </td>
            </tr>
            @endforeach
        </x-table>
        @else
        <div class="px-6 py-12 text-center">
            <p class="text-sm text-gray-500">No invoices yet</p>
        </div>
        @endif
    </x-card>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartDefaults = {
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
                        color: 'rgba(255, 255, 255, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#9A9A9A',
                        font: {
                            size: 10,
                            weight: '600'
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#9A9A9A',
                        font: {
                            size: 10,
                            weight: '600'
                        }
                    }
                }
            }
        };

        // Monthly Revenue Chart
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx && revenueCtx.dataset.labels) {
            const labels = JSON.parse(revenueCtx.dataset.labels);
            const values = JSON.parse(revenueCtx.dataset.values);

            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue',
                        data: values,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#3B82F6',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    ...chartDefaults,
                    plugins: {
                        ...chartDefaults.plugins,
                        tooltip: {
                            backgroundColor: '#111111',
                            titleColor: '#9A9A9A',
                            bodyColor: '#FFFFFF',
                            borderColor: '#222222',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 12,
                            callbacks: {
                                label: (context) => ' KES ' + context.parsed.y.toLocaleString()
                            }
                        }
                    }
                }
            });
        }

        // Invoice Status Distribution Chart
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx && statusCtx.dataset.values) {
            const statusData = JSON.parse(statusCtx.dataset.values);
            const statusColors = {
                'paid': '#10B981',
                'sent': '#3B82F6',
                'pending': '#F59E0B',
                'overdue': '#EF4444',
                'draft': '#6B7280'
            };

            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusData),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: Object.keys(statusData).map(s => statusColors[s.toLowerCase()] || '#6B7280'),
                        borderWidth: 0,
                        hoverOffset: 20
                    }]
                },
                options: {
                    ...chartDefaults,
                    cutout: '75%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                color: '#9A9A9A',
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 11,
                                    weight: '700'
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection