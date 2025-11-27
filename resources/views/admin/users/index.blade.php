@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Users</h1>
        <p class="mt-1 text-sm text-gray-600">Manage all system users</p>
    </div>

    @if(isset($users) && $users->count() > 0)
        <x-card padding="none">
            <x-table>
                <x-slot name="header">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verified</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </x-slot>
                @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user['name'] ?? 'Unknown' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user['email'] ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-badge :variant="$user['role'] === 'admin' ? 'primary' : 'default'">{{ ucfirst($user['role'] ?? 'user') }}</x-badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user['email_verified_at'])
                                <x-badge variant="success">Verified</x-badge>
                            @else
                                <x-badge variant="warning">Pending</x-badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.users.edit', $user['id']) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>
    @else
        <x-card>
            <div class="text-center py-12">
                <p class="text-sm text-gray-500">No users found</p>
            </div>
        </x-card>
    @endif
</div>
@endsection

