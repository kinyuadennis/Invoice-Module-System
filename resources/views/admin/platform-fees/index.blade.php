@extends('layouts.admin')

@section('title', 'Platform Fees')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Platform Fees</h1>
        <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Track all platform fees collected</p>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('admin.platform-fees.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-select name="status" label="Status" :options="[
                ['value' => '', 'label' => 'All Statuses'],
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'paid', 'label' => 'Paid'],
            ]" value="{{ request('status') }}" />
            <x-select name="company_id" label="Company" :options="array_merge([['value' => '', 'label' => 'All Companies']], $companies->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray())" value="{{ request('company_id') }}" />
            <div class="flex items-end">
                <x-button type="submit" variant="primary" class="w-full">Filter</x-button>
            </div>
        </form>
    </x-card>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
            <div class="text-center">
                <p class="text-sm font-bold text-gray-500 dark:text-[#9A9A9A] uppercase tracking-wider">Total Collected</p>
                <p class="mt-2 text-3xl font-black text-gray-900 dark:text-white tracking-tight">KES {{ number_format($stats['total_collected'] ?? 0, 2) }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
            <div class="text-center">
                <p class="text-sm font-bold text-gray-500 dark:text-[#9A9A9A] uppercase tracking-wider">Pending</p>
                <p class="mt-2 text-3xl font-black text-gray-900 dark:text-white tracking-tight">{{ $stats['pending'] ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
            <div class="text-center">
                <p class="text-sm font-bold text-gray-500 dark:text-[#9A9A9A] uppercase tracking-wider">Paid</p>
                <p class="mt-2 text-3xl font-black text-gray-900 dark:text-white tracking-tight">{{ $stats['paid'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    @if(isset($fees) && $fees->count() > 0)
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Invoice</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Client</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Company</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">User</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Fee Amount</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#2A2A2A]">
                    @foreach($fees as $fee)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                            {{ $fee['invoice']['invoice_reference'] ?? $fee['invoice']['invoice_number'] ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600 dark:text-[#9A9A9A]">
                            {{ $fee['invoice']['client']['name'] ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600 dark:text-gray-300">
                            @if($fee['invoice']['company'])
                            <a href="{{ route('admin.companies.show', $fee['invoice']['company']['id']) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs uppercase tracking-wider">
                                {{ $fee['invoice']['company']['name'] }}
                            </a>
                            @else
                            <span class="text-gray-400 font-medium">No Company</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600 dark:text-gray-300">
                            {{ $fee['invoice']['user']['name'] ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-gray-900 dark:text-white">
                            KES {{ number_format($fee['fee_amount'] ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                            $statusVariant = match(strtolower($fee['fee_status'] ?? 'pending')) {
                            'paid' => 'success',
                            'pending' => 'warning',
                            default => 'default'
                            };
                            $statusColor = match(strtolower($fee['fee_status'] ?? 'pending')) {
                            'paid' => 'text-emerald-600 bg-emerald-500/10 border-emerald-500/20',
                            'pending' => 'text-amber-600 bg-amber-500/10 border-amber-500/20',
                            default => 'text-gray-600 bg-gray-500/10 border-gray-500/20'
                            };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest border {{ $statusColor }}">
                                {{ ucfirst($fee['fee_status'] ?? 'pending') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="text-center py-12">
        <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">No platform fees found</p>
    </div>
    @endif
</div>
@endsection