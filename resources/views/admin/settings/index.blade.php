@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="max-w-4xl space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
        <p class="mt-1 text-sm text-gray-600">Configure platform-wide settings</p>
    </div>

    <x-card>
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Platform Fee Configuration</h2>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3 border-b border-gray-200">
                <div>
                    <p class="text-sm font-medium text-gray-900">Fee Rate</p>
                    <p class="text-sm text-gray-600">{{ $platformFeeSettings['rate'] ?? 0 }}%</p>
                </div>
            </div>
            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-sm font-medium text-gray-900">Total Fees Collected</p>
                    <p class="text-sm text-gray-600">${{ number_format($platformFeeSettings['total_collected'] ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
    </x-card>
</div>
@endsection

