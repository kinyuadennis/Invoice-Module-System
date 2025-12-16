<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Invoice $invoice,
        public string $reminderType // 'due_soon' or 'overdue'
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $invoiceNumber = $this->invoice->invoice_number ?? 'INV-'.str_pad($this->invoice->id, 3, '0', STR_PAD_LEFT);
        $companyName = $this->invoice->company->name ?? 'InvoiceHub';

        $subject = match ($this->reminderType) {
            'overdue' => "Payment Overdue: Invoice #{$invoiceNumber} from {$companyName}",
            default => "Payment Reminder: Invoice #{$invoiceNumber} from {$companyName}",
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $daysOverdue = $this->invoice->due_date
            ? now()->diffInDays($this->invoice->due_date, false)
            : 0;

        return new Content(
            view: 'emails.payment-reminder',
            with: [
                'invoice' => $this->invoice,
                'client' => $this->invoice->client,
                'company' => $this->invoice->company,
                'invoiceNumber' => $this->invoice->invoice_number ?? 'INV-'.str_pad($this->invoice->id, 3, '0', STR_PAD_LEFT),
                'total' => $this->invoice->grand_total ?? $this->invoice->total,
                'dueDate' => $this->invoice->due_date,
                'reminderType' => $this->reminderType,
                'daysOverdue' => $daysOverdue,
            ],
        );
    }
}
