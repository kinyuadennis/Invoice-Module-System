@extends('layouts.admin')

@section('title', 'All Payments')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">All Payments</h1>
        <p class="mt-1 text-sm text-gray-600">View all payment transactions</p>
    </div>

    @if(isset($payments) && $payments->count() > 0)
        <x-card padding="none">
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    </tr>
                </x-slot>
                @foreach($payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $payment['payment_date'] ? \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('admin.invoices.show', $payment['invoice_id'] ?? 0) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                {{ $payment['invoice']['invoice_number'] ?? 'N/A' }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $payment['invoice']['client']['name'] ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $payment['invoice']['user']['name'] ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                            ${{ number_format($payment['amount'] ?? 0, 2) }}
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>
    @else
        <x-card>
            <div class="text-center py-12">
                <p class="text-sm text-gray-500">No payments found</p>
            </div>
        </x-card>
    @endif
</div>
@endsection

