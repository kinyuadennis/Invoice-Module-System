@extends('layouts.app')

@section('title', 'Clients')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Clients</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Manage your client database</p>
        </div>
        <a href="{{ route('clients.create') }}">
            <x-button variant="primary">
                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Client
            </x-button>
        </a>
    </div>

    <!-- Clients Table -->
    @if(isset($clients) && $clients->count() > 0)
        <x-card padding="none">
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoices</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </x-slot>
                @foreach($clients as $client)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $client['name'] ?? 'Unknown' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-600 dark:text-gray-300">{{ $client['email'] ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-600 dark:text-gray-300">{{ $client['phone'] ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-600 dark:text-gray-300">{{ $client['invoices_count'] ?? 0 }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('clients.show', $client['id']) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                <a href="{{ route('clients.edit', $client['id']) }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900">Edit</a>
                                @if(($client['invoices_count'] ?? 0) === 0)
                                    <form method="POST" action="{{ route('clients.destroy', $client['id']) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this client?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <!-- Pagination -->
            @if(method_exists($clients, 'links'))
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $clients->links() }}
                </div>
            @endif
        </x-card>
    @else
        <x-card>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No clients</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by adding your first client.</p>
                <div class="mt-6">
                    <a href="{{ route('clients.create') }}">
                        <x-button variant="primary">Add Client</x-button>
                    </a>
                </div>
            </div>
        </x-card>
    @endif
</div>
@endsection

