@extends('layouts.user')

@section('title', 'Roles & Permissions')

@section('content')
<div class="space-y-6 mb-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Roles & Permissions</h1>
            <p class="mt-1 text-sm text-gray-600">Manage user roles and permissions for your company</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('user.roles.create') }}">
                <x-button variant="primary">
                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Role
                </x-button>
            </a>
        </div>
    </div>

    <!-- Roles Table -->
    @if(isset($roles) && $roles->count() > 0)
        <x-card padding="none">
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Permissions</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Users</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </x-slot>
                @foreach($roles as $role)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $role['name'] }}</p>
                                    @if($role['is_system'] ?? false)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                            System Role
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-600">
                            {{ $role['description'] ?? '-' }}
                        </td>
                        <td class="px-5 py-3 text-center text-sm text-gray-600">
                            {{ $role['permissions_count'] ?? 0 }}
                        </td>
                        <td class="px-5 py-3 text-center text-sm text-gray-600">
                            {{ $role['users_count'] ?? 0 }}
                        </td>
                        <td class="px-5 py-3">
                            @if($role['is_active'] ?? true)
                                <x-badge variant="success">Active</x-badge>
                            @else
                                <x-badge variant="danger">Inactive</x-badge>
                            @endif
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('user.roles.show', $role['id']) }}" class="text-indigo-600 hover:text-indigo-900" title="View">View</a>
                                @if(!($role['is_system'] ?? false))
                                    <a href="{{ route('user.roles.edit', $role['id']) }}" class="text-gray-600 hover:text-gray-900" title="Edit">Edit</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>
    @else
        <x-card>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-3-3h-2a3 3 0 00-3 3v2zM13 10a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No roles</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new role.</p>
                <div class="mt-6">
                    <a href="{{ route('user.roles.create') }}">
                        <x-button variant="primary">New Role</x-button>
                    </a>
                </div>
            </div>
        </x-card>
    @endif
</div>
@endsection

