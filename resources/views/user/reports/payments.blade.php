@extends('layouts.user')

@section('title', 'Payment Report')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Payment Report</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Track payments and payment methods</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('user.reports.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300">
                Back to Reports
            </a>
        </div>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('user.reports.payments') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $filters['start_date'] ?? '' }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $filters['end_date'] ?? '' }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Apply Filters</button>
            </div>
        </form>
    </x-card>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-card>
            <p class="text-sm text-gray-500 mb-1">Total Payments</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($report['summary']['total_payments'], 2) }}</p>
        </x-card>
        <x-card>
            <p class="text-sm text-gray-500 mb-1">Payment Count</p>
            <p class="text-2xl font-bold text-gray-900">{{ $report['summary']['payment_count'] }}</p>
        </x-card>
        <x-card>
            <p class="text-sm text-gray-500 mb-1">Average Payment</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($report['summary']['average_payment'], 2) }}</p>
        </x-card>
    </div>

    <!-- Monthly Breakdown Chart -->
    @if(count($report['monthly_breakdown']) > 0)
    <x-card>
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Monthly Payment Trend</h2>
        <canvas id="paymentChart" height="100"></canvas>
    </x-card>
    @endif

    <!-- Payment Method Breakdown -->
    @if(count($report['method_breakdown']) > 0)
    <x-card>
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Methods</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($report['method_breakdown'] as $method => $data)
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500 mb-1">{{ ucfirst($method ?: 'Unknown') }}</p>
                    <p class="text-xl font-bold text-gray-900">{{ $data['count'] }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ number_format($data['total'], 2) }}</p>
                </div>
            @endforeach
        </div>
    </x-card>
    @endif

    <!-- Payment List -->
    <x-card padding="none">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Recent Payments</h2>
        </div>
        @if($report['payments']->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($report['payments']->take(50) as $payment)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ $payment->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('user.invoices.show', $payment->invoice) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                        {{ $payment->invoice->invoice_number ?? $payment->invoice->invoice_reference }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->invoice->client->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ number_format($payment->amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ ucfirst($payment->payment_method ?: 'N/A') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($report['payments']->count() > 50)
                <div class="px-6 py-4 border-t border-gray-200 text-center">
                    <p class="text-sm text-gray-500">Showing first 50 of {{ $report['payments']->count() }} payments</p>
                </div>
            @endif
        @else
            <div class="px-6 py-12 text-center">
                <p class="text-sm text-gray-500">No payments found for the selected filters</p>
            </div>
        @endif
    </x-card>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    @if(count($report['monthly_breakdown']) > 0)
    const ctx = document.getElementById('paymentChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json(array_column($report['monthly_breakdown'], 'month')),
            datasets: [{
                label: 'Payments',
                data: @json(array_column($report['monthly_breakdown'], 'amount')),
                backgroundColor: 'rgba(139, 92, 246, 0.5)',
                borderColor: 'rgb(139, 92, 246)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
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
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'KES ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    @endif
</script>
@endpush
@endsection

