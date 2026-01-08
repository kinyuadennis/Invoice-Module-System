@extends('layouts.user')

@section('title', 'Inventory')

@section('content')
<div class="space-y-6 mb-6">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Inventory</h1>
            <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Manage your inventory and track stock levels</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('user.inventory.create') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 uppercase tracking-wider">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Item
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    @if(isset($stats))
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Total Items</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ $stats['total_items'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-500/10 rounded-xl">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Low Stock</p>
                    <p class="text-2xl font-black text-red-600 dark:text-red-400 tracking-tight">{{ $stats['low_stock_count'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-red-100 dark:bg-red-500/10 rounded-xl">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Out of Stock</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ $stats['out_of_stock_count'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-xl">
                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Stock Value</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">KES {{ number_format($stats['total_stock_value'] ?? 0, 2) }}</p>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-500/10 rounded-xl">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm">
        <form method="GET" action="{{ route('user.inventory.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-5">
            <div>
                <label for="search" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Search</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Name, SKU, barcode..."
                    class="block w-full rounded-xl border-gray-200 dark:border-[#333333] dark:bg-black/50 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5" />
            </div>

            <div>
                <label for="category" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Category</label>
                <select
                    id="category"
                    name="category"
                    class="block w-full rounded-xl border-gray-200 dark:border-[#333333] dark:bg-black/50 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="stock_status" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Stock Status</label>
                <select
                    id="stock_status"
                    name="stock_status"
                    class="block w-full rounded-xl border-gray-200 dark:border-[#333333] dark:bg-black/50 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5">
                    <option value="">All</option>
                    <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                </select>
            </div>

            <div>
                <label for="is_active" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Status</label>
                <select
                    id="is_active"
                    name="is_active"
                    class="block w-full rounded-xl border-gray-200 dark:border-[#333333] dark:bg-black/50 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5">
                    <option value="">All</option>
                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-transparent shadow-sm text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 uppercase tracking-wider">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Inventory Items Table -->
    @if(isset($inventoryItems) && $inventoryItems->count() > 0)
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Item</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">SKU</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Category</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Stock</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Cost Price</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Selling Price</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#2A2A2A]">
                    @foreach($inventoryItems as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $item['name'] }}</p>
                                @if($item['is_low_stock'] ?? false)
                                <span class="inline-flex mt-1 items-center px-2 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-400 uppercase tracking-widest w-fit">
                                    Low Stock
                                </span>
                                @endif
                                @if($item['is_out_of_stock'] ?? false)
                                <span class="inline-flex mt-1 items-center px-2 py-0.5 rounded text-[10px] font-black bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-400 uppercase tracking-widest w-fit">
                                    Out of Stock
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            {{ $item['sku'] ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            {{ $item['category'] ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if($item['track_stock'] ?? false)
                            <span class="font-bold {{ ($item['is_low_stock'] ?? false) ? 'text-amber-600 dark:text-amber-400' : (($item['is_out_of_stock'] ?? false) ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white') }}">
                                {{ number_format($item['current_stock'], 2) }} {{ $item['unit_of_measure'] ?? 'pcs' }}
                            </span>
                            @else
                            <span class="text-gray-400 dark:text-[#9A9A9A] text-xs uppercase tracking-wider">Not tracked</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-300">
                            KES {{ number_format($item['cost_price'], 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900 dark:text-white">
                            KES {{ number_format($item['selling_price'], 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                <a href="{{ route('user.inventory.show', $item['id']) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs uppercase tracking-wider" title="View">View</a>
                                <a href="{{ route('user.inventory.edit', $item['id']) }}" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 font-bold text-xs uppercase tracking-wider" title="Edit">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(method_exists($inventoryItems, 'links'))
        <div class="px-6 py-4 border-t border-gray-100 dark:border-[#2A2A2A]">
            {{ $inventoryItems->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-12 text-center shadow-sm">
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-[#9A9A9A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No inventory items</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-[#9A9A9A]">Get started by adding your first inventory item.</p>
        <div class="mt-6">
            <a href="{{ route('user.inventory.create') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 uppercase tracking-wider">
                New Item
            </a>
        </div>
    </div>
    @endif
</div>
@endsection