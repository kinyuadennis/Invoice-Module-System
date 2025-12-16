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
        <x-card padding="sm">
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

        <x-card padding="sm">
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
                    </dl>
                </div>
            </div>
        </x-card>

        <x-card padding="sm">
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
                    </dl>
                </div>
            </div>
        </x-card>

        <x-card padding="sm">
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
@endsection

