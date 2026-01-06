@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Audit Logs</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">System activity and compliance tracking</p>
        </div>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="action" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Action</label>
                <select name="action" id="action" class="w-full rounded-md border-gray-300 dark:border-[#404040] shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="model_type" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Model Type</label>
                <select name="model_type" id="model_type" class="w-full rounded-md border-gray-300 dark:border-[#404040] shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Types</option>
                    @foreach($modelTypes as $type)
                        <option value="{{ $type }}" {{ request('model_type') === $type ? 'selected' : '' }}>{{ class_basename($type) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">User</label>
                <select name="user_id" id="user_id" class="w-full rounded-md border-gray-300 dark:border-[#404040] shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">From Date</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="w-full rounded-md border-gray-300 dark:border-[#404040] shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">To Date</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="w-full rounded-md border-gray-300 dark:border-[#404040] shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="md:col-span-5 flex gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Filter
                </button>
                <a href="{{ route('admin.audit-logs.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Clear
                </a>
            </div>
        </form>
    </x-card>

    <!-- Audit Logs Table -->
    <x-card padding="none">
        @if($auditLogs->count() > 0)
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </x-slot>
                @foreach($auditLogs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->created_at->format('M d, Y H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->user ? $log->user->name : 'System' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-badge :variant="match($log->action) {
                                'created' => 'success',
                                'updated' => 'info',
                                'deleted' => 'danger',
                                default => 'default'
                            }">{{ ucfirst($log->action) }}</x-badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            {{ class_basename($log->model_type) }}
                            @if($log->model_id)
                                <span class="text-gray-400">#{{ $log->model_id }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ Str::limit($log->description ?? 'N/A', 50) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $log->ip_address ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.audit-logs.show', $log->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                View Details
                            </a>
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200 dark:border-[#333333]">
                {{ $auditLogs->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <p class="text-sm text-gray-500">No audit logs found</p>
            </div>
        @endif
    </x-card>
</div>
@endsection


