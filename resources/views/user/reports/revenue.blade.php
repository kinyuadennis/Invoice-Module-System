@extends('layouts.user')

@section('title', 'Revenue Report')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Revenue Report</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Track revenue trends and performance</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('user.reports.export.revenue-csv', $filters) }}" class="px-4 py-2 bg-gray-200 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300">
                Export CSV
            </a>
            <a href="{{ route('user.reports.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300">
                Back to Reports
            </a>
        </div>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('user.reports.revenue') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
            <p class="text-sm text-gray-500 mb-1">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($report['summary']['total_revenue'], 2) }}</p>
        </x-card>
        <x-card>
            <p class="text-sm text-gray-500 mb-1">Total Invoices</p>
            <p class="text-2xl font-bold text-gray-900">{{ $report['summary']['total_invoices'] }}</p>
        </x-card>
        <x-card>
            <p class="text-sm text-gray-500 mb-1">Average Invoice Value</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($report['summary']['average_invoice_value'], 2) }}</p>
        </x-card>
    </div>

    <!-- Monthly Breakdown Chart -->
    @if(count($report['monthly_breakdown']) > 0)
    <x-card>
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Monthly Revenue Trend</h2>
        <canvas id="revenueChart" height="100"></canvas>
    </x-card>
    @endif

    <!-- Top Clients -->
    @if(count($report['top_clients']) > 0)
    <x-card>
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Top Clients by Revenue</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoices</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($report['top_clients'] as $client)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $client->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ number_format($client->total_revenue, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ $client->invoice_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    @if(count($report['monthly_breakdown']) > 0)
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json(array_column($report['monthly_breakdown'], 'month')),
            datasets: [{
                label: 'Revenue',
                data: @json(array_column($report['monthly_breakdown'], 'revenue')),
                borderColor: 'rgb(99, 102, 241)',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4
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

