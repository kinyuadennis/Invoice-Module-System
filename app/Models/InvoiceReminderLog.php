<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceReminderLog extends Model
{
    protected $fillable = [
        'invoice_id',
        'company_id',
        'reminder_type',
        'sent_at',
        'recipient_email',
        'sent_successfully',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'sent_successfully' => 'boolean',
    ];

    /**
     * The invoice this reminder log belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * The company this reminder log belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if a reminder was sent recently (within X days).
     */
    public static function wasSentRecently(int $invoiceId, string $reminderType, int $days = 7): bool
    {
        return self::where('invoice_id', $invoiceId)
            ->where('reminder_type', $reminderType)
            ->where('sent_successfully', true)
            ->where('sent_at', '>=', now()->subDays($days))
            ->exists();
    }
}
