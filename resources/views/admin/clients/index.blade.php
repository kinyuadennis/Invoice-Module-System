@extends('layouts.admin')

@section('title', 'Clients')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">All Clients</h1>
            <p class="mt-1 text-sm text-gray-600">Manage all clients in the system</p>
        </div>
        <a href="{{ route('admin.clients.create') }}">
            <x-button variant="primary">Add Client</x-button>
        </a>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('admin.clients.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-select name="company_id" label="Company" :options="array_merge([['value' => '', 'label' => 'All Companies']], $companies->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray())" value="{{ request('company_id') }}" />
            <div class="flex items-end">
                <x-button type="submit" variant="primary" class="w-full">Filter</x-button>
            </div>
        </form>
    </x-card>

    @if(isset($clients) && $clients->count() > 0)
        <x-card padding="none">
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoices</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </x-slot>
                @foreach($clients as $client)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $client['name'] ?? 'Unknown' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $client['email'] ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $client['phone'] ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @if($client['company'])
                                <a href="{{ route('admin.companies.show', $client['company']['id']) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $client['company']['name'] }}
                                </a>
                            @else
                                <span class="text-gray-400">No Company</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $client['invoices_count'] ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.clients.edit', $client['id']) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>
    @else
        <x-card>
            <div class="text-center py-12">
                <p class="text-sm text-gray-500">No clients found</p>
            </div>
        </x-card>
    @endif
    @else
        <x-card>
            <div class="text-center py-12">
                <p class="text-sm text-gray-500">No clients found</p>
            </div>
        </x-card>
    @endif
</div>
@endsection

