@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<!-- Page Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">User Management</h1>
        <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Control access and system roles</p>
    </div>
    <div class="flex items-center gap-3">
        <span class="flex items-center gap-2 px-3 py-1.5 bg-blue-500/5 border border-blue-500/10 rounded-full">
            <div class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></div>
            <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest px-1">Authority Active</span>
        </span>
    </div>
</div>

<!-- Filters -->
<x-card class="mb-8 overflow-visible">
    <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 items-end">
        <div class="lg:col-span-2">
            <x-select name="company_id" label="Filter by Entity" :options="array_merge([['value' => '', 'label' => 'All Companies']], $companies->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray())" value="{{ request('company_id') }}" />
        </div>
        <div class="lg:col-span-2 flex gap-3">
            <x-button type="submit" variant="primary" class="flex-1 btn-ripple !rounded-xl !h-[42px] font-bold">
                Apply Filters
            </x-button>
            @if(request()->hasAny(['company_id']))
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-50 dark:bg-[#111111] border border-gray-200 dark:border-[#222222] rounded-xl text-sm font-bold text-gray-700 dark:text-[#D4D4D4] hover:bg-gray-100 flex items-center transition-all h-[42px]">
                Reset
            </a>
            @endif
        </div>
    </form>
</x-card>

@if(isset($users) && $users->count() > 0)
<x-card padding="none" class="overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table-modern">
            <thead>
                <tr>
                    <th class="font-black uppercase tracking-widest text-[10px] text-gray-400 dark:text-[#9A9A9A]">User Identity</th>
                    <th class="font-black uppercase tracking-widest text-[10px] text-gray-400 dark:text-[#9A9A9A]">Entity Affiliation</th>
                    <th class="font-black uppercase tracking-widest text-[10px] text-gray-400 dark:text-[#9A9A9A]">Access Role</th>
                    <th class="font-black uppercase tracking-widest text-[10px] text-gray-400 dark:text-[#9A9A9A]">Status</th>
                    <th class="text-right font-black uppercase tracking-widest text-[10px] text-gray-400 dark:text-[#9A9A9A]">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="group hover:bg-gray-50/50 dark:hover:bg-[#0F0F0F]/50 transition-colors">
                    <td class="py-4">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500/10 to-indigo-500/10 border border-blue-500/20 flex items-center justify-center text-blue-500 font-black text-sm">
                                    {{ strtoupper(substr($user['name'], 0, 1)) }}
                                </div>
                                @if($user['email_verified_at'])
                                <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white dark:border-[#1A1A1A] flex items-center justify-center">
                                    <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                @endif
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-blue-500 transition-colors">{{ $user['name'] ?? 'Unknown' }}</div>
                                <div class="text-xs font-mono text-gray-500 dark:text-[#9A9A9A]">{{ $user['email'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($user['company'])
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-[#111111] border border-white/5 flex items-center justify-center text-[8px] font-black text-gray-400">
                                {{ strtoupper(substr($user['company']['name'], 0, 1)) }}
                            </div>
                            <a href="{{ route('admin.companies.show', $user['company']['id']) }}" class="text-xs font-bold text-gray-700 dark:text-[#D4D4D4] hover:text-blue-500">
                                {{ $user['company']['name'] }}
                            </a>
                        </div>
                        @else
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-2 opacity-50">Global / None</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge-{{ $user['role'] === 'admin' ? 'indigo' : 'gray' }} text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg">
                            {{ $user['role'] ?? 'user' }}
                        </span>
                    </td>
                    <td>
                        @if($user['email_verified_at'])
                        <span class="flex items-center gap-1.5 text-emerald-500">
                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                            <span class="text-[10px] font-black uppercase tracking-widest">Verified</span>
                        </span>
                        @else
                        <span class="flex items-center gap-1.5 text-amber-500">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-500"></div>
                            <span class="text-[10px] font-black uppercase tracking-widest">Pending</span>
                        </span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.users.edit', $user['id']) }}" class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-500/5 rounded-xl transition-all" title="Edit User">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(method_exists($users, 'links'))
    <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5">
        {{ $users->links() }}
    </div>
    @endif
</x-card>
@else
<x-card>
    <div class="text-center py-24">
        <div class="w-16 h-16 bg-gray-50 dark:bg-[#111111] border border-gray-100 dark:border-white/5 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No users found</h3>
        <p class="text-sm text-gray-500">Try adjusting your filters to find who you're looking for.</p>
    </div>
</x-card>
@endif
</div>
@endsection