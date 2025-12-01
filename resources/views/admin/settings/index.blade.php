@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="max-w-4xl space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
        <p class="mt-1 text-sm text-gray-600">Configure platform-wide settings</p>
    </div>

    <x-card>
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Platform Fee Configuration</h2>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3 border-b border-gray-200">
                <div>
                    <p class="text-sm font-medium text-gray-900">Fee Rate</p>
                    <p class="text-sm text-gray-600">Currently set to {{ $platformFeeSettings['rate'] ?? 0.8 }}% of invoice total</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-semibold text-gray-900">{{ $platformFeeSettings['rate'] ?? 0.8 }}%</p>
                </div>
            </div>
            <div class="flex items-center justify-between py-3 border-b border-gray-200">
                <div>
                    <p class="text-sm font-medium text-gray-900">Total Fees Collected</p>
                    <p class="text-sm text-gray-600">Fees that have been paid</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-semibold text-gray-900">KES {{ number_format($platformFeeSettings['total_collected'] ?? 0, 2) }}</p>
                </div>
            </div>
            <div class="flex items-center justify-between py-3 border-b border-gray-200">
                <div>
                    <p class="text-sm font-medium text-gray-900">Pending Fees</p>
                    <p class="text-sm text-gray-600">Fees awaiting payment</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-semibold text-gray-900">KES {{ number_format($platformFeeSettings['pending'] ?? 0, 2) }}</p>
                </div>
            </div>
            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-sm font-medium text-gray-900">Total Fees Generated</p>
                    <p class="text-sm text-gray-600">All fees across all invoices</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-semibold text-gray-900">KES {{ number_format($platformFeeSettings['total'] ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="mt-6 pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-500">
                Note: Platform fee rate is currently configured in the application code. To change the rate, update the configuration file.
            </p>
        </div>
    </x-card>
</div>
@endsection

