@extends('layouts.admin')

@section('title', 'All Invoices')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">All Invoices</h1>
        <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">View and manage invoices from all users</p>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('admin.invoices.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <x-input type="text" name="search" label="Search" value="{{ request('search') }}" placeholder="Invoice #, client, user..." />
            <x-select name="status" label="Status" :options="[
                ['value' => '', 'label' => 'All Statuses'],
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'sent', 'label' => 'Sent'],
                ['value' => 'paid', 'label' => 'Paid'],
                ['value' => 'overdue', 'label' => 'Overdue'],
            ]" value="{{ request('status') }}" />
            <x-select name="company_id" label="Company" :options="array_merge([['value' => '', 'label' => 'All Companies']], $companies->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray())" value="{{ request('company_id') }}" />
            <div class="flex items-end">
                <x-button type="submit" variant="primary" class="w-full">Filter</x-button>
            </div>
        </form>
    </x-card>

    @if(isset($invoices) && $invoices->count() > 0)
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Invoice #</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Client</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Company</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">User</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Amount</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#2A2A2A]">
                    @foreach($invoices as $invoice)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('admin.invoices.show', $invoice['id']) }}" class="text-sm font-black text-[#2B6EF6] hover:text-[#2563EB] transition-colors duration-150">
                                {{ $invoice['invoice_reference'] ?? $invoice['invoice_number'] ?? 'INV-' . str_pad($invoice['id'], 3, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ $invoice['client']['name'] ?? 'Unknown' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($invoice['company'])
                            <a href="{{ route('admin.companies.show', $invoice['company']['id']) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                {{ $invoice['company']['name'] }}
                            </a>
                            @else
                            <span class="text-sm font-medium text-gray-400">No Company</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">
                            {{ $invoice['user']['name'] ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                            $statusClasses = match(strtolower($invoice['status'] ?? 'draft')) {
                            'paid' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 ring-emerald-500/20',
                            'sent' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 ring-blue-500/20',
                            'overdue' => 'bg-red-500/10 text-red-600 dark:text-red-400 ring-red-500/20',
                            'pending' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 ring-amber-500/20',
                            default => 'bg-gray-500/10 text-gray-600 dark:text-gray-400 ring-gray-500/20'
                            };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest ring-1 {{ $statusClasses }}">
                                {{ ucfirst($invoice['status'] ?? 'draft') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-gray-900 dark:text-white">
                            KES {{ number_format($invoice['grand_total'] ?? $invoice['total'] ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.invoices.show', $invoice['id']) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs uppercase tracking-wider">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <x-card>
        <div class="text-center py-12">
            <p class="text-sm text-gray-500">No invoices found</p>
        </div>
    </x-card>
    @endif
</div>
@endsection