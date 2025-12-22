<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Expense extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'category_id',
        'client_id',
        'invoice_id',
        'recurring_expense_id',
        'expense_number',
        'amount',
        'expense_date',
        'description',
        'notes',
        'receipt_path',
        'payment_method',
        'status',
        'tax_deductible',
        'tax_amount',
        'reference_number',
        'vendor_name',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
            'tax_deductible' => 'boolean',
            'tax_amount' => 'decimal:2',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($expense) {
            if (empty($expense->expense_number)) {
                $expense->expense_number = 'EXP-'.strtoupper(Str::random(8));
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recurringExpense(): BelongsTo
    {
        return $this->belongsTo(Expense::class, 'recurring_expense_id');
    }
}
