@extends('layouts.user')

@section('title', 'Edit Role')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Edit Role</h1>
        <p class="mt-1 text-sm text-gray-600">Update role details and permissions</p>
    </div>

    <form method="POST" action="{{ route('user.roles.update', $role['id']) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Basic Information</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input 
                        type="text" 
                        name="name" 
                        label="Role Name *" 
                        value="{{ old('name', $role['name']) }}"
                        required
                    />
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea 
                        name="description" 
                        rows="3"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    >{{ old('description', $role['description'] ?? '') }}</textarea>
                </div>

                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        id="is_active"
                        value="1"
                        {{ ($role['is_active'] ?? true) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">Active</label>
                </div>
            </div>
        </x-card>

        <!-- Permissions -->
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Permissions</h2>
            <p class="text-sm text-gray-600 mb-4">Select the permissions this role should have</p>
            
            <div class="space-y-6">
                @foreach($permissions as $category => $categoryPermissions)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 capitalize">{{ str_replace('-', ' ', $category) }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($categoryPermissions as $permission)
                                <div class="flex items-start">
                                    <input 
                                        type="checkbox" 
                                        name="permissions[]" 
                                        id="permission_{{ $permission->id }}"
                                        value="{{ $permission->id }}"
                                        class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        @php
                                            $rolePermissions = [];
                                            if (isset($role['permissions'])) {
                                                if (is_array($role['permissions'])) {
                                                    $rolePermissions = collect($role['permissions'])->pluck('id')->toArray();
                                                } elseif (method_exists($role['permissions'], 'pluck')) {
                                                    $rolePermissions = $role['permissions']->pluck('id')->toArray();
                                                }
                                            }
                                        @endphp
                                        {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                    >
                                    <label for="permission_{{ $permission->id }}" class="ml-2 text-sm text-gray-700">
                                        <span class="font-medium">{{ $permission->display_name }}</span>
                                        @if($permission->description)
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $permission->description }}</p>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('user.roles.show', $role['id']) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <x-button type="submit" variant="primary">Update Role</x-button>
        </div>
    </form>
</div>
@endsection

