@extends('layouts.admin')

@section('title', 'Companies')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">All Companies</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Manage all companies in the system</p>
    </div>

    @if(isset($companies) && $companies->count() > 0)
        <x-card padding="none">
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clients</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoices</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </x-slot>
                @foreach($companies as $company)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($company['logo'])
                                    <img src="{{ Storage::url($company['logo']) }}" alt="{{ $company['name'] }}" class="h-10 w-10 rounded-full object-cover mr-3">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                        <span class="text-indigo-600 font-semibold">{{ strtoupper(substr($company['name'], 0, 1)) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.companies.show', $company['id']) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                                        {{ $company['name'] }}
                                    </a>
                                    @if($company['email'])
                                        <p class="text-xs text-gray-500">{{ $company['email'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            @if($company['owner'])
                                <div>
                                    <div class="font-medium">{{ $company['owner']['name'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $company['owner']['email'] }}</div>
                                </div>
                            @else
                                <span class="text-gray-400">No Owner</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ $company['users_count'] ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ $company['clients_count'] ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ $company['invoices_count'] ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                            KES {{ number_format($company['revenue'] ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.companies.show', $company['id']) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                            <a href="{{ route('admin.companies.edit', $company['id']) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>

        <div class="mt-4">
            {{ $companies->links() }}
        </div>
    @else
        <x-card>
            <div class="text-center py-12">
                <p class="text-sm text-gray-500">No companies found</p>
            </div>
        </x-card>
    @endif
</div>
@endsection

