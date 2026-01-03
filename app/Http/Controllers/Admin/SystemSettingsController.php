<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class SystemSettingsController extends Controller
{
    /**
     * Display system settings.
     */
    public function index()
    {
        return view('admin.settings.index', [
            'settings' => $this->getSystemSettings(),
        ]);
    }

    /**
     * Update system settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'default_currency' => 'sometimes|string|max:3',
            'default_timezone' => 'sometimes|string|max:50',
            'vat_rate' => 'sometimes|numeric|min:0|max:100',
            'invoice_prefix_default' => 'sometimes|string|max:10',
            'max_invoice_items' => 'sometimes|integer|min:1|max:1000',
            'enable_email_notifications' => 'sometimes|boolean',
            'enable_sms_notifications' => 'sometimes|boolean',
        ]);

        // Store settings in cache and config (in production, use database)
        foreach ($validated as $key => $value) {
            Cache::forever("system_setting_{$key}", $value);
            Config::set("system.{$key}", $value);
        }

        // Log the settings update
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'model_type' => 'SystemSettings',
            'model_id' => null,
            'description' => 'Updated system settings',
            'old_values' => $this->getSystemSettings(),
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.settings.index')
            ->with('success', 'System settings updated successfully.');
    }

    /**
     * Get current system settings.
     */
    protected function getSystemSettings(): array
    {
        return [
            'default_currency' => Cache::get('system_setting_default_currency', 'KES'),
            'default_timezone' => Cache::get('system_setting_default_timezone', 'Africa/Nairobi'),
            'vat_rate' => Cache::get('system_setting_vat_rate', 16.0),
            'invoice_prefix_default' => Cache::get('system_setting_invoice_prefix_default', 'INV'),
            'max_invoice_items' => Cache::get('system_setting_max_invoice_items', 100),
            'enable_email_notifications' => Cache::get('system_setting_enable_email_notifications', true),
            'enable_sms_notifications' => Cache::get('system_setting_enable_sms_notifications', false),
        ];
    }
}
