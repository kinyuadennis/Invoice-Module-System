<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringInvoice extends Model
{
    protected $fillable = [
        'company_id',
        'client_id',
        'user_id',
        'name',
        'description',
        'frequency',
        'interval',
        'start_date',
        'end_date',
        'next_run_date',
        'last_generated_at',
        'status',
        'invoice_data',
        'auto_send',
        'send_reminders',
        'total_generated',
        'max_occurrences',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'next_run_date' => 'date',
            'last_generated_at' => 'date',
            'invoice_data' => 'array',
            'auto_send' => 'boolean',
            'send_reminders' => 'boolean',
            'interval' => 'integer',
            'total_generated' => 'integer',
            'max_occurrences' => 'integer',
        ];
    }

    /**
     * The company this recurring invoice belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The client this recurring invoice is for.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * The user who created this recurring invoice.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Invoices generated from this recurring template.
     */
    public function generatedInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'recurring_invoice_id');
    }

    /**
     * Scope to get active recurring invoices.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get recurring invoices due for generation.
     */
    public function scopeDueForGeneration($query)
    {
        return $query->where('status', 'active')
            ->where('next_run_date', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('max_occurrences')
                    ->orWhereColumn('total_generated', '<', 'max_occurrences');
            });
    }

    /**
     * Check if this recurring invoice should be generated today.
     */
    public function isDue(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->next_run_date > now()->toDateString()) {
            return false;
        }

        if ($this->end_date && $this->end_date < now()->toDateString()) {
            return false;
        }

        if ($this->max_occurrences && $this->total_generated >= $this->max_occurrences) {
            return false;
        }

        return true;
    }

    /**
     * Calculate the next run date based on frequency and interval.
     */
    public function calculateNextRunDate(): \Carbon\Carbon
    {
        $date = $this->next_run_date ?? $this->start_date;

        return match ($this->frequency) {
            'daily' => $date->addDays($this->interval),
            'weekly' => $date->addWeeks($this->interval),
            'monthly' => $date->addMonths($this->interval),
            'yearly' => $date->addYears($this->interval),
            default => $date->addMonth(),
        };
    }
}
