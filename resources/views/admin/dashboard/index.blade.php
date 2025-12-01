@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="mt-1 text-sm text-gray-600">Overview of all system activity</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-card padding="sm">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Companies</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $stats['totalCompanies'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <x-card padding="sm">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $stats['totalUsers'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <x-card padding="sm">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Clients</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $stats['totalClients'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <x-card padding="sm">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Invoices</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $stats['totalInvoices'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </x-card>

        <x-card padding="sm">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
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
    </div>

    <!-- Top Companies -->
    @if(isset($topCompanies) && $topCompanies->count() > 0)
        <x-card padding="none">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Top Companies by Revenue</h2>
                    <p class="text-sm text-gray-600">Highest earning companies</p>
                </div>
                <a href="{{ route('admin.companies.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">View all</a>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach($topCompanies as $company)
                    <div class="px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                @if($company['logo'])
                                    <img src="{{ Storage::url($company['logo']) }}" alt="{{ $company['name'] }}" class="h-10 w-10 rounded-full object-cover">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-indigo-600 font-semibold">{{ strtoupper(substr($company['name'], 0, 1)) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.companies.show', $company['id']) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                                        {{ $company['name'] }}
                                    </a>
                                    <p class="text-xs text-gray-500">{{ $company['invoices_count'] }} invoices, {{ $company['users_count'] }} users</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">KES {{ number_format($company['revenue'], 2) }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif

    <!-- Recent Companies -->
    @if(isset($recentCompanies) && $recentCompanies->count() > 0)
        <x-card padding="none">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Recent Companies</h2>
                    <p class="text-sm text-gray-600">Newly registered companies</p>
                </div>
                <a href="{{ route('admin.companies.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">View all</a>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach($recentCompanies as $company)
                    <div class="px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                @if($company['logo'])
                                    <img src="{{ Storage::url($company['logo']) }}" alt="{{ $company['name'] }}" class="h-10 w-10 rounded-full object-cover">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-indigo-600 font-semibold">{{ strtoupper(substr($company['name'], 0, 1)) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.companies.show', $company['id']) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                                        {{ $company['name'] }}
                                    </a>
                                    <p class="text-xs text-gray-500">Owner: {{ $company['owner'] }} • {{ $company['created_at']->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif

    <!-- Recent Invoices -->
    <x-card padding="none">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Recent Invoices</h2>
                <p class="text-sm text-gray-600">Latest invoices across all users</p>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @if($invoice['company'])
                                <a href="{{ route('admin.companies.show', $invoice['company']['id']) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $invoice['company']['name'] }}
                                </a>
                            @else
                                <span class="text-gray-400">No Company</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
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
@endsection

