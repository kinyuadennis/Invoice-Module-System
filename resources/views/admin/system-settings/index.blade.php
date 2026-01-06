@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div>
        <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Configure system-wide defaults and preferences</p>
    </div>

    <!-- Settings Form -->
    <form method="POST" action="{{ route('admin.system-settings.update') }}">
        @csrf
        @method('PUT')

        <x-card>
            <div class="space-y-6">
                <!-- Currency & Timezone -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="default_currency" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Default Currency
                        </label>
                        <input type="text" name="default_currency" id="default_currency" value="{{ old('default_currency', $settings['default_currency'] ?? 'KES') }}" maxlength="3" class="w-full rounded-md border-gray-300 dark:border-[#404040] shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="KES">
                        <p class="mt-1 text-xs text-gray-500">ISO 4217 currency code (e.g., KES, USD, EUR)</p>
                        @error('default_currency')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="default_timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Default Timezone
                        </label>
                        <select name="default_timezone" id="default_timezone" class="w-full rounded-md border-gray-300 dark:border-[#404040] shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Africa/Nairobi" {{ old('default_timezone', $settings['default_timezone'] ?? 'Africa/Nairobi') === 'Africa/Nairobi' ? 'selected' : '' }}>Africa/Nairobi</option>
                            <option value="UTC" {{ old('default_timezone', $settings['default_timezone'] ?? 'Africa/Nairobi') === 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="America/New_York" {{ old('default_timezone', $settings['default_timezone'] ?? 'Africa/Nairobi') === 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                            <option value="Europe/London" {{ old('default_timezone', $settings['default_timezone'] ?? 'Africa/Nairobi') === 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                        </select>
                        @error('default_timezone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Tax & Fees -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="vat_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Default VAT Rate (%)
                        </label>
                        <input type="number" name="vat_rate" id="vat_rate" value="{{ old('vat_rate', $settings['vat_rate'] ?? 16.0) }}" step="0.01" min="0" max="100" class="w-full rounded-md border-gray-300 dark:border-[#404040] shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Default VAT rate for new companies</p>
                        @error('vat_rate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <!-- Invoice Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="invoice_prefix_default" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Default Invoice Prefix
                        </label>
                        <input type="text" name="invoice_prefix_default" id="invoice_prefix_default" value="{{ old('invoice_prefix_default', $settings['invoice_prefix_default'] ?? 'INV') }}" maxlength="10" class="w-full rounded-md border-gray-300 dark:border-[#404040] shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Default prefix for new invoices</p>
                        @error('invoice_prefix_default')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="max_invoice_items" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Max Invoice Items
                        </label>
                        <input type="number" name="max_invoice_items" id="max_invoice_items" value="{{ old('max_invoice_items', $settings['max_invoice_items'] ?? 100) }}" min="1" max="1000" class="w-full rounded-md border-gray-300 dark:border-[#404040] shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Maximum line items per invoice</p>
                        @error('max_invoice_items')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="enable_email_notifications" value="1" {{ old('enable_email_notifications', $settings['enable_email_notifications'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-[#404040] text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">Enable Email Notifications</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500">Allow system to send email notifications</p>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="enable_sms_notifications" value="1" {{ old('enable_sms_notifications', $settings['enable_sms_notifications'] ?? false) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-[#404040] text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">Enable SMS Notifications</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500">Allow system to send SMS notifications</p>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-[#333333]">
                    <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Save Settings
                    </button>
                </div>
            </div>
        </x-card>
    </form>
</div>
@endsection


