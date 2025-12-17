@extends('layouts.user')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
        <div class="flex items-center gap-3">
            @if(isset($companies) && $companies->count() > 0)
                <a href="{{ route('user.companies.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors" title="Add another company">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden md:inline">Add Company</span>
                </a>
            @endif
            <a href="{{ route('user.invoices.create') }}" id="dashboard-new-invoice-btn">
            <x-button variant="primary">
                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Invoice
            </x-button>
        </a>
        </div>
    </div>
    
    @if(!auth()->user()->onboarding_completed || (isset($companies) && $companies->count() === 0))
        <!-- Quick Setup Card -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6" id="dashboard-quick-setup">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Complete Your Setup</h3>
                    <p class="text-sm text-gray-600 mb-4">
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
                <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-card padding="sm" class="hover:shadow-md transition-shadow cursor-pointer" onclick="window.location.href='{{ route('user.invoices.index', ['status' => 'paid']) }}'">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-[#2B6EF6] rounded-lg p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                        <dd class="text-lg font-semibold text-gray-900">KES {{ number_format($stats['totalRevenue'] ?? 0, 2) }}</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <x-card padding="sm" class="hover:shadow-md transition-shadow cursor-pointer" onclick="window.location.href='{{ route('user.invoices.index', ['status' => 'sent']) }}'">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-lg p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Outstanding</dt>
                        <dd class="text-lg font-semibold text-gray-900">KES {{ number_format($stats['outstanding'] ?? 0, 2) }}</dd>
                        <dd class="text-xs text-gray-500 mt-1">{{ $stats['outstandingCount'] ?? 0 }} invoice(s)</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <x-card padding="sm" class="hover:shadow-md transition-shadow cursor-pointer" onclick="window.location.href='{{ route('user.invoices.index', ['status' => 'overdue']) }}'">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-500 rounded-lg p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Overdue</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $stats['overdueCount'] ?? 0 }}</dd>
                        <dd class="text-xs text-red-600 mt-1">KES {{ number_format($stats['overdue'] ?? 0, 2) }}</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <x-card padding="sm" class="hover:shadow-md transition-shadow cursor-pointer" onclick="window.location.href='{{ route('user.invoices.index', ['status' => 'paid']) }}'">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-lg p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Paid Invoices</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $stats['paidCount'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </x-card>
    </div>
    
    <!-- Status Distribution & Quick Filters -->
    @if(isset($statusDistribution) && count($statusDistribution) > 0)
        <x-card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Invoice Status Overview</h2>
                <div class="flex gap-2">
                    <a href="{{ route('user.invoices.index', ['status' => 'draft']) }}" class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Draft
                    </a>
                    <a href="{{ route('user.invoices.index', ['status' => 'sent']) }}" class="px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200">
                        Sent
                    </a>
                    <a href="{{ route('user.invoices.index', ['status' => 'paid']) }}" class="px-3 py-1.5 text-xs font-medium text-green-700 bg-green-100 rounded-lg hover:bg-green-200">
                        Paid
                    </a>
                    <a href="{{ route('user.invoices.index', ['status' => 'overdue']) }}" class="px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 rounded-lg hover:bg-red-200">
                        Overdue
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Status Cards -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($statusDistribution as $status)
                        @if($status['count'] > 0)
                            <div class="text-center p-4 rounded-lg border border-gray-200 hover:border-gray-300 transition-colors">
                                <div class="text-2xl font-bold mb-1" style="color: {{ $status['color'] }}">
                                    {{ $status['count'] }}
                                </div>
                                <div class="text-sm font-medium text-gray-700 mb-1">{{ $status['name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $status['percentage'] }}%</div>
                                <div class="mt-2 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" style="background-color: {{ $status['color'] }}; width: {{ $status['percentage'] }}%"></div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <!-- Status Pie Chart -->
                <div style="position: relative; height: 250px; max-width: 100%;">
                    <canvas id="statusChart"></canvas>
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
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Revenue Trends (Last 12 Months)</h2>
                    <div style="position: relative; height: 300px; max-width: 100%;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </x-card>
            @endif

            <!-- Invoice Aging Report -->
            @if(isset($insights['invoice_aging']))
                <x-card>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Invoice Aging Report</h2>
                    <div class="space-y-4">
                        @foreach($insights['invoice_aging'] as $ageGroup => $data)
                            @if($data['count'] > 0)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $ageGroup }} Days</p>
                                        <p class="text-xs text-gray-500">{{ $data['count'] }} invoice(s)</p>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900">KES {{ number_format($data['amount'], 2) }}</p>
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
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Days Sales Outstanding (DSO)</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">30-Day DSO</span>
                            <span class="text-2xl font-bold text-gray-900">{{ number_format($insights['dso'], 1) }} days</span>
                        </div>
                        @if(isset($insights['dso_90']))
                            <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                                <span class="text-sm text-gray-600">90-Day DSO</span>
                                <span class="text-2xl font-bold text-gray-900">{{ number_format($insights['dso_90'], 1) }} days</span>
                            </div>
                        @endif
                        @if(isset($insights['avg_payment_time']))
                            <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                                <span class="text-sm text-gray-600">Average Payment Time</span>
                                <span class="text-2xl font-bold text-gray-900">{{ number_format($insights['avg_payment_time'], 1) }} days</span>
                            </div>
                        @endif
                    </div>
                </x-card>
            @endif

            <!-- Top Clients -->
            @if(isset($insights['top_clients']) && count($insights['top_clients']) > 0)
                <x-card>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Top Clients by Revenue</h2>
                    <div class="space-y-3">
                        @foreach(array_slice($insights['top_clients'], 0, 5) as $client)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $client['client_name'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $client['invoice_count'] }} invoice(s)</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">KES {{ number_format($client['revenue'], 2) }}</p>
                                    @if($client['avg_payment_time'])
                                        <p class="text-xs text-gray-500">{{ number_format($client['avg_payment_time'], 1) }} days avg</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>
    @endif

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

    <!-- Recent Invoices -->
    <x-card padding="none">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Recent Invoices</h2>
                <p class="text-sm text-gray-600">Latest activity across your invoices</p>
            </div>
            <a href="{{ route('user.invoices.index') }}" class="text-sm text-[#2B6EF6] hover:text-[#2563EB] font-medium">View all</a>
        </div>

        @if(isset($recentInvoices) && count($recentInvoices) > 0)
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    </tr>
                </x-slot>
                @foreach($recentInvoices as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('user.invoices.show', $invoice['id']) }}" class="text-sm font-medium text-[#2B6EF6] hover:text-[#2563EB]">
                                {{ $invoice['invoice_number'] ?? 'INV-' . str_pad($invoice['id'], 3, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $invoice['client']['name'] ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusService = new \App\Http\Services\InvoiceStatusService();
                                $statusVariant = $statusService::getStatusVariant($invoice['status'] ?? 'draft');
                                $statusInfo = $statusService::getStatuses()[$invoice['status'] ?? 'draft'] ?? ['label' => ucfirst($invoice['status'] ?? 'draft')];
                            @endphp
                            <x-badge :variant="$statusVariant" title="{{ $statusInfo['description'] ?? '' }}">
                                {{ $statusInfo['label'] ?? ucfirst($invoice['status'] ?? 'draft') }}
                            </x-badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $invoice['due_date'] ? \Carbon\Carbon::parse($invoice['due_date'])->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                            KES {{ number_format($invoice['total'] ?? 0, 2) }}
                        </td>
                    </tr>
                @endforeach
            </x-table>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No invoices</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating your first invoice.</p>
                <div class="mt-6">
                    <a href="{{ route('user.invoices.create') }}">
                        <x-button variant="primary">New Invoice</x-button>
                    </a>
                </div>
            </div>
        @endif
    </x-card>
</div>

@push('scripts')
    @if(isset($insights) && isset($insights['revenue_trends']) && count($insights['revenue_trends']) > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            (function() {
                // Store chart instances to prevent re-initialization
                let revenueChartInstance = null;
                let statusChartInstance = null;

                function initCharts() {
                    // Revenue Chart
                    const ctx = document.getElementById('revenueChart');
                    if (ctx && !revenueChartInstance) {
                        try {
                            const revenueData = @json($insights['revenue_trends']);
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
                        } catch (error) {
                            console.error('Error initializing revenue chart:', error);
                        }
                    }

                    // Status Distribution Pie Chart
                    const statusCtx = document.getElementById('statusChart');
                    if (statusCtx && !statusChartInstance) {
                        try {
                            const statusData = @json($statusDistribution ?? []);
                            statusChartInstance = new Chart(statusCtx, {
                                type: 'doughnut',
                                data: {
                                    labels: statusData.map(item => item.name),
                                    datasets: [{
                                        data: statusData.map(item => item.count),
                                        backgroundColor: statusData.map(item => item.bgColor),
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
                        } catch (error) {
                            console.error('Error initializing status chart:', error);
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
    @endif

<script>
    // Dashboard Tour
    (function() {
        const onboardingCompleted = {{ auth()->user()->onboarding_completed ? 'true' : 'false' }};
        const tourSeen = localStorage.getItem('dashboard-tour-seen');
        
        if (onboardingCompleted && tourSeen !== 'true') {
            // Simple inline tour implementation
            setTimeout(() => {
                const steps = [
                    {
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
    <x-feedback-form />
@endsection

