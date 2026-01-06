@extends('layouts.user')

@section('title', 'Role Details')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $role['name'] }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">View role details and manage users</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(!($role['is_system'] ?? false))
                <a href="{{ route('user.roles.edit', $role['id']) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
            @endif
        </div>
    </div>

    <!-- Role Details Card -->
    <x-card>
        <div class="space-y-6">
            <!-- Role Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Role Information</h3>
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="text-gray-500">Name:</span>
                            <span class="font-medium text-gray-900 ml-2">{{ $role['name'] }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Slug:</span>
                            <span class="font-medium text-gray-900 ml-2">{{ $role['slug'] }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Status:</span>
                            @if($role['is_active'] ?? true)
                                <x-badge variant="success" class="ml-2">Active</x-badge>
                            @else
                                <x-badge variant="danger" class="ml-2">Inactive</x-badge>
                            @endif
                        </div>
                        @if($role['is_system'] ?? false)
                            <div>
                                <span class="text-gray-500">Type:</span>
                                <x-badge variant="info" class="ml-2">System Role</x-badge>
                            </div>
                        @endif
                        @if(isset($role['description']))
                            <div>
                                <span class="text-gray-500">Description:</span>
                                <p class="text-gray-900 mt-1">{{ $role['description'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Statistics</h3>
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="text-gray-500">Permissions:</span>
                            <span class="font-medium text-gray-900 ml-2">{{ $role['permissions_count'] ?? 0 }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Users with this role:</span>
                            <span class="font-medium text-gray-900 ml-2">{{ $role['users_count'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            @if(isset($role['permissions']) && (is_countable($role['permissions']) ? count($role['permissions']) : 0) > 0)
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Assigned Permissions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @php
                            $permissions = is_array($role['permissions']) ? collect($role['permissions']) : $role['permissions'];
                            $groupedPermissions = $permissions->groupBy('category');
                        @endphp
                        @foreach($groupedPermissions as $category => $categoryPermissions)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-2 capitalize">{{ str_replace('-', ' ', $category) }}</h4>
                                <ul class="space-y-1">
                                    @foreach($categoryPermissions as $permission)
                                        <li class="text-sm text-gray-600 dark:text-gray-300 flex items-center">
                                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            {{ is_array($permission) ? $permission['display_name'] : $permission->display_name }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="border-t border-gray-200 pt-6">
                    <p class="text-sm text-gray-500">No permissions assigned to this role.</p>
                </div>
            @endif

            <!-- Users with this Role -->
            @if(isset($role['users']) && (is_countable($role['users']) ? count($role['users']) : 0) > 0)
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Users with this Role</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($role['users'] as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $user['name'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                            {{ $user['email'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <form method="POST" action="{{ route('user.roles.remove-user', $role['id']) }}" class="inline" onsubmit="return confirm('Remove this role from {{ $user['name'] }}?');">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user['id'] }}">
                                                <button type="submit" class="text-red-600 hover:text-red-900">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500">No users assigned to this role.</p>
                        <form method="POST" action="{{ route('user.roles.assign-user', $role['id']) }}" class="flex items-center gap-2">
                            @csrf
                            <select name="user_id" required class="rounded-lg border-gray-300 text-sm">
                                <option value="">Select a user...</option>
                                @foreach(\App\Models\User::whereHas('company', function($q) use ($role) {
                                    $q->where('id', auth()->user()->active_company_id ?? auth()->user()->company_id);
                                })->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                Assign User
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </x-card>
</div>
@endsection

