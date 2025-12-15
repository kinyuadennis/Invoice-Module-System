<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * InvoiceAuditLog Model
 *
 * Records all critical actions on invoices for audit and compliance.
 * This model is read-only from the application perspective (logs are created, never updated).
 */
class InvoiceAuditLog extends Model
{
    protected $fillable = [
        'invoice_id',
        'user_id',
        'action_type',
        'old_data',
        'new_data',
        'metadata',
        'ip_address',
        'user_agent',
        'source',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Action types constants.
     */
    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_FINALIZE = 'finalize';
    public const ACTION_SEND = 'send';
    public const ACTION_PAY = 'pay';
    public const ACTION_PDF_GENERATE = 'PDF_generate';
    public const ACTION_API_ACCESS = 'API_access';
    public const ACTION_ETIMS_EXPORT = 'ETIMS_export';
    public const ACTION_ACCOUNTING_EXPORT = 'accounting_export';
    public const ACTION_CANCEL = 'cancel';
    public const ACTION_VOID = 'void';

    /**
     * Source constants.
     */
    public const SOURCE_UI = 'ui';
    public const SOURCE_API = 'api';
    public const SOURCE_INTEGRATION = 'integration';
    public const SOURCE_JOB = 'job';

    /**
     * The invoice this log entry belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * The user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by action type.
     */
    public function scopeActionType($query, string $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Scope to filter by source.
     */
    public function scopeSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
