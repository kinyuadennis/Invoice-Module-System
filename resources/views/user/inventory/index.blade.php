@extends('layouts.user')

@section('title', 'Inventory')

@section('content')
<div class="space-y-6 mb-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Inventory</h1>
            <p class="mt-1 text-sm text-gray-600">Manage your inventory and track stock levels</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('user.inventory.create') }}">
                <x-button variant="primary">
                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Item
                </x-button>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    @if(isset($stats))
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Items</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_items'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
            </x-card>
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Low Stock</p>
                        <p class="text-2xl font-bold text-red-600">{{ $stats['low_stock_count'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </x-card>
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Out of Stock</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['out_of_stock_count'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-gray-100 rounded-lg">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
            </x-card>
            <x-card padding="sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Stock Value</p>
                        <p class="text-2xl font-bold text-gray-900">KES {{ number_format($stats['total_stock_value'] ?? 0, 2) }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </x-card>
        </div>
    @endif

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('user.inventory.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-5">
            <div>
                <x-input 
                    type="text" 
                    name="search" 
                    label="Search" 
                    value="{{ request('search') }}"
                    placeholder="Name, SKU, barcode..."
                />
            </div>
            
            <div>
                <x-select 
                    name="category" 
                    label="Category"
                    :options="array_merge([['value' => '', 'label' => 'All Categories']], $categories->map(function($c) { return ['value' => $c, 'label' => $c]; })->toArray())"
                    value="{{ request('category') }}"
                />
            </div>

            <div>
                <x-select 
                    name="stock_status" 
                    label="Stock Status"
                    :options="[
                        ['value' => '', 'label' => 'All'],
                        ['value' => 'low_stock', 'label' => 'Low Stock'],
                        ['value' => 'out_of_stock', 'label' => 'Out of Stock'],
                        ['value' => 'in_stock', 'label' => 'In Stock'],
                    ]"
                    value="{{ request('stock_status') }}"
                />
            </div>

            <div>
                <x-select 
                    name="is_active" 
                    label="Status"
                    :options="[
                        ['value' => '', 'label' => 'All'],
                        ['value' => '1', 'label' => 'Active'],
                        ['value' => '0', 'label' => 'Inactive'],
                    ]"
                    value="{{ request('is_active') }}"
                />
            </div>

            <div class="flex items-end">
                <x-button type="submit" variant="primary" class="w-full">Filter</x-button>
            </div>
        </form>
    </x-card>

    <!-- Inventory Items Table -->
    @if(isset($inventoryItems) && $inventoryItems->count() > 0)
        <x-card padding="none">
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Cost Price</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Selling Price</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </x-slot>
                @foreach($inventoryItems as $item)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $item['name'] }}</p>
                                    @if($item['is_low_stock'] ?? false)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">
                                            Low Stock
                                        </span>
                                    @endif
                                    @if($item['is_out_of_stock'] ?? false)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mt-1">
                                            Out of Stock
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-600">
                            {{ $item['sku'] ?? '-' }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-600">
                            {{ $item['category'] ?? '-' }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right text-sm font-medium">
                            @if($item['track_stock'] ?? false)
                                <span class="{{ ($item['is_low_stock'] ?? false) ? 'text-yellow-600' : (($item['is_out_of_stock'] ?? false) ? 'text-red-600' : 'text-gray-900') }}">
                                    {{ number_format($item['current_stock'], 2) }} {{ $item['unit_of_measure'] ?? 'pcs' }}
                                </span>
                            @else
                                <span class="text-gray-400">Not tracked</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right text-sm text-gray-600">
                            KES {{ number_format($item['cost_price'], 2) }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                            KES {{ number_format($item['selling_price'], 2) }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('user.inventory.show', $item['id']) }}" class="text-indigo-600 hover:text-indigo-900" title="View">View</a>
                                <a href="{{ route('user.inventory.edit', $item['id']) }}" class="text-gray-600 hover:text-gray-900" title="Edit">Edit</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <!-- Pagination -->
            @if(method_exists($inventoryItems, 'links'))
                <div class="px-5 py-3 border-t border-gray-200">
                    {{ $inventoryItems->links() }}
                </div>
            @endif
        </x-card>
    @else
        <x-card>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No inventory items</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by adding your first inventory item.</p>
                <div class="mt-6">
                    <a href="{{ route('user.inventory.create') }}">
                        <x-button variant="primary">New Item</x-button>
                    </a>
                </div>
            </div>
        </x-card>
    @endif
</div>
@endsection

