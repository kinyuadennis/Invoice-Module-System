@extends('layouts.admin')

@section('title', 'Audit Log Details')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Audit Log Details</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Detailed information about this system action</p>
        </div>
        <a href="{{ route('admin.audit-logs.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300">
            Back to Logs
        </a>
    </div>

    <!-- Main Details -->
    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">Timestamp</h3>
                <p class="text-sm text-gray-900">{{ $auditLog->created_at->format('F d, Y H:i:s') }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">User</h3>
                <p class="text-sm text-gray-900">{{ $auditLog->user ? $auditLog->user->name . ' (' . $auditLog->user->email . ')' : 'System' }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">Action</h3>
                <x-badge :variant="match($auditLog->action) {
                    'created' => 'success',
                    'updated' => 'info',
                    'deleted' => 'danger',
                    default => 'default'
                }">{{ ucfirst($auditLog->action) }}</x-badge>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">Model Type</h3>
                <p class="text-sm text-gray-900">{{ $auditLog->model_type }}</p>
            </div>

            @if($auditLog->model_id)
            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">Model ID</h3>
                <p class="text-sm text-gray-900">#{{ $auditLog->model_id }}</p>
            </div>
            @endif

            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-1">IP Address</h3>
                <p class="text-sm text-gray-900">{{ $auditLog->ip_address ?? 'N/A' }}</p>
            </div>

            @if($auditLog->user_agent)
            <div class="md:col-span-2">
                <h3 class="text-sm font-medium text-gray-500 mb-1">User Agent</h3>
                <p class="text-sm text-gray-900 break-all">{{ $auditLog->user_agent }}</p>
            </div>
            @endif

            @if($auditLog->description)
            <div class="md:col-span-2">
                <h3 class="text-sm font-medium text-gray-500 mb-1">Description</h3>
                <p class="text-sm text-gray-900">{{ $auditLog->description }}</p>
            </div>
            @endif
        </div>
    </x-card>

    <!-- Changes -->
    @if($auditLog->old_values || $auditLog->new_values)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @if($auditLog->old_values)
        <x-card>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Old Values</h3>
            <pre class="bg-gray-50 p-4 rounded-md text-xs overflow-auto max-h-96">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
        </x-card>
        @endif

        @if($auditLog->new_values)
        <x-card>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">New Values</h3>
            <pre class="bg-gray-50 p-4 rounded-md text-xs overflow-auto max-h-96">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
        </x-card>
        @endif
    </div>
    @endif
</div>
@endsection


