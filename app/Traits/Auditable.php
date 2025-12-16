<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    /**
     * Log an action to the audit log.
     */
    public static function logAction(string $action, ?self $model = null, ?string $description = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => static::class,
            'model_id' => $model?->id,
            'description' => $description ?? self::generateDescription($action, $model),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Generate a description for the audit log entry.
     */
    protected static function generateDescription(string $action, ?self $model): string
    {
        $modelName = class_basename(static::class);
        $identifier = $model ? ($model->id ?? 'new') : 'unknown';

        return match ($action) {
            'created' => "Created {$modelName} #{$identifier}",
            'updated' => "Updated {$modelName} #{$identifier}",
            'deleted' => "Deleted {$modelName} #{$identifier}",
            'viewed' => "Viewed {$modelName} #{$identifier}",
            default => ucfirst($action)." {$modelName} #{$identifier}",
        };
    }

    /**
     * Boot the trait and set up model events.
     */
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            static::logAction('created', $model);
        });

        static::updated(function ($model) {
            $oldValues = $model->getOriginal();
            $newValues = $model->getChanges();
            static::logAction('updated', $model, null, $oldValues, $newValues);
        });

        static::deleted(function ($model) {
            static::logAction('deleted', $model);
        });
    }
}
