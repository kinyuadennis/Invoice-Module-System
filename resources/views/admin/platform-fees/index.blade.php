@extends('layouts.admin')

@section('title', 'Platform Fees')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Platform Fees</h1>
        <p class="mt-1 text-sm text-gray-600">Track all platform fees collected</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <x-card padding="sm">
            <div class="text-center">
                <p class="text-sm font-medium text-gray-500">Total Collected</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($stats['total_collected'] ?? 0, 2) }}</p>
            </div>
        </x-card>
        <x-card padding="sm">
            <div class="text-center">
                <p class="text-sm font-medium text-gray-500">Pending</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['pending'] ?? 0 }}</p>
            </div>
        </x-card>
        <x-card padding="sm">
            <div class="text-center">
                <p class="text-sm font-medium text-gray-500">Paid</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['paid'] ?? 0 }}</p>
            </div>
        </x-card>
    </div>

    @if(isset($fees) && $fees->count() > 0)
        <x-card padding="none">
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Fee Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </x-slot>
                @foreach($fees as $fee)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $fee['invoice']['invoice_number'] ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $fee['invoice']['client']['name'] ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $fee['invoice']['user']['name'] ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                            ${{ number_format($fee['fee_amount'] ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusVariant = match(strtolower($fee['fee_status'] ?? 'pending')) {
                                    'paid' => 'success',
                                    'pending' => 'warning',
                                    default => 'default'
                                };
                            @endphp
                            <x-badge :variant="$statusVariant">{{ ucfirst($fee['fee_status'] ?? 'pending') }}</x-badge>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>
    @else
        <x-card>
            <div class="text-center py-12">
                <p class="text-sm text-gray-500">No platform fees found</p>
            </div>
        </x-card>
    @endif
</div>
@endsection

