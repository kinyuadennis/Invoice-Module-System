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
        $company = $this->invoice->company;
        $companyName = $company->name ?? 'InvoiceHub';

        // Use custom subject if company has custom templates enabled
        $subject = match ($this->reminderType) {
            'overdue' => "Payment Overdue: Invoice #{$invoiceNumber} from {$companyName}",
            default => "Payment Reminder: Invoice #{$invoiceNumber} from {$companyName}",
        };

        if ($company && $company->use_custom_email_templates && $company->email_template_payment_reminder_subject) {
            $subject = $this->replaceTemplateVariables($company->email_template_payment_reminder_subject);
        }

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $company = $this->invoice->company;
        $invoiceNumber = $this->invoice->invoice_number ?? 'INV-'.str_pad($this->invoice->id, 3, '0', STR_PAD_LEFT);
        $total = $this->invoice->grand_total ?? $this->invoice->total;
        $dueDate = $this->invoice->due_date;
        $daysOverdue = $dueDate ? now()->diffInDays($dueDate, false) : 0;

        $view = 'emails.payment-reminder';
        $with = [
            'invoice' => $this->invoice,
            'client' => $this->invoice->client,
            'company' => $company,
            'invoiceNumber' => $invoiceNumber,
            'total' => $total,
            'dueDate' => $dueDate,
            'reminderType' => $this->reminderType,
            'daysOverdue' => $daysOverdue,
        ];

        if ($company && $company->use_custom_email_templates && $company->email_template_payment_reminder_body) {
            $with['customBody'] = $this->replaceTemplateVariables($company->email_template_payment_reminder_body);
            $view = 'emails.custom-template';
        }

        return new Content(
            view: $view,
            with: $with,
        );
    }

    /**
     * Replace template variables in custom email templates.
     */
    protected function replaceTemplateVariables(string $template): string
    {
        $invoiceNumber = $this->invoice->invoice_number ?? 'INV-'.str_pad($this->invoice->id, 3, '0', STR_PAD_LEFT);
        $company = $this->invoice->company;
        $client = $this->invoice->client;
        $total = $this->invoice->grand_total ?? $this->invoice->total;
        $dueDate = $this->invoice->due_date ? \Carbon\Carbon::parse($this->invoice->due_date)->format('F d, Y') : 'N/A';
        $daysOverdue = $this->invoice->due_date ? now()->diffInDays($this->invoice->due_date, false) : 0;

        $replacements = [
            '{{invoice_number}}' => $invoiceNumber,
            '{{company_name}}' => $company->name ?? 'InvoiceHub',
            '{{client_name}}' => $client->name ?? 'Valued Client',
            '{{total}}' => number_format($total, 2),
            '{{due_date}}' => $dueDate,
            '{{days_overdue}}' => $daysOverdue > 0 ? $daysOverdue : 0,
            '{{reminder_type}}' => $this->reminderType === 'overdue' ? 'overdue' : 'due soon',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
