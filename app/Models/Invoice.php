<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
        'status',
        'invoice_reference',
        'issue_date',
        'due_date',
        'payment_method',
        'payment_details',
        'notes',
        'subtotal',
        'tax',
        'total',
    ];

    /**
     * The client this invoice belongs to.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * The user who created this invoice.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The invoice items/line items belonging to this invoice.
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * The payments made for this invoice.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * The platform fees charged on this invoice.
     */
    public function platformFees()
    {
        return $this->hasMany(PlatformFee::class);
    }

    /**
     * Scope for filtering invoices by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
