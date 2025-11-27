@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Edit User</h1>
    </div>

    <x-card>
        <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <x-input type="text" name="name" label="Name" value="{{ old('name', $user->name) }}" required />
            <x-input type="email" name="email" label="Email" value="{{ old('email', $user->email) }}" required />

            <x-select name="role" label="Role" :options="[
                ['value' => 'user', 'label' => 'User'],
                ['value' => 'admin', 'label' => 'Admin'],
                ['value' => 'staff', 'label' => 'Staff'],
            ]" :value="old('role', $user->role)" required />

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('admin.users.index') }}">
                    <x-button type="button" variant="outline">Cancel</x-button>
                </a>
                <x-button type="submit" variant="primary">Update User</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection

