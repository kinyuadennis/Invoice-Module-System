@extends('layouts.admin')

@section('title', 'Companies')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">All Companies</h1>
        <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Manage all companies in the system</p>
    </div>

    @if(isset($companies) && $companies->count() > 0)
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Company</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Owner</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Users</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Clients</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Invoices</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Revenue</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#2A2A2A]">
                    @foreach($companies as $company)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                @if($company['logo'])
                                <img src="{{ Storage::url($company['logo']) }}" alt="{{ $company['name'] }}" class="h-10 w-10 rounded-xl object-cover ring-1 ring-gray-900/5 dark:ring-white/10">
                                @else
                                <div class="h-10 w-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center ring-1 ring-indigo-500/10">
                                    <span class="text-indigo-600 dark:text-indigo-400 font-black text-xs">{{ strtoupper(substr($company['name'], 0, 1)) }}</span>
                                </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.companies.show', $company['id']) }}" class="text-sm font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        {{ $company['name'] }}
                                    </a>
                                    @if($company['email'])
                                    <p class="text-xs font-medium text-gray-500 dark:text-[#9A9A9A]">{{ $company['email'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            @if($company['owner'])
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white">{{ $company['owner']['name'] }}</div>
                                <div class="text-xs font-medium text-gray-500 dark:text-[#9A9A9A]">{{ $company['owner']['email'] }}</div>
                            </div>
                            @else
                            <span class="text-gray-400 font-medium">No Owner</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600 dark:text-gray-300">{{ $company['users_count'] ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600 dark:text-gray-300">{{ $company['clients_count'] ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600 dark:text-gray-300">{{ $company['invoices_count'] ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-gray-900 dark:text-white">
                            KES {{ number_format($company['revenue'] ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.companies.show', $company['id']) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs uppercase tracking-wider mr-3">View</a>
                            <a href="{{ route('admin.companies.edit', $company['id']) }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-bold text-xs uppercase tracking-wider">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $companies->links() }}
    </div>
    @else
    <div class="text-center py-12">
        <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">No companies found</p>
    </div>
    @endif
</div>
@endsection