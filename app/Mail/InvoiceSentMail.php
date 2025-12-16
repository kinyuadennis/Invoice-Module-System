<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceSentMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Invoice $invoice,
        public string $pdfPath,
        public ?string $accessUrl = null
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
        $subject = "Invoice #{$invoiceNumber} from {$companyName}";
        if ($company && $company->use_custom_email_templates && $company->email_template_invoice_sent_subject) {
            $subject = $this->replaceTemplateVariables($company->email_template_invoice_sent_subject);
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

        // Use custom body if company has custom templates enabled
        $view = 'emails.invoice-sent';
        $with = [
            'invoice' => $this->invoice,
            'client' => $this->invoice->client,
            'company' => $company,
            'invoiceNumber' => $invoiceNumber,
            'total' => $total,
            'dueDate' => $dueDate,
            'accessUrl' => $this->accessUrl,
        ];

        if ($company && $company->use_custom_email_templates && $company->email_template_invoice_sent_body) {
            // For custom templates, we'll use a special view that renders the custom HTML
            $with['customBody'] = $this->replaceTemplateVariables($company->email_template_invoice_sent_body);
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
        $issueDate = $this->invoice->issue_date ? \Carbon\Carbon::parse($this->invoice->issue_date)->format('F d, Y') : 'N/A';

        $replacements = [
            '{{invoice_number}}' => $invoiceNumber,
            '{{company_name}}' => $company->name ?? 'InvoiceHub',
            '{{client_name}}' => $client->name ?? 'Valued Client',
            '{{total}}' => number_format($total, 2),
            '{{due_date}}' => $dueDate,
            '{{issue_date}}' => $issueDate,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $invoiceNumber = $this->invoice->invoice_number ?? 'INV-'.str_pad($this->invoice->id, 3, '0', STR_PAD_LEFT);

        return [
            Attachment::fromPath($this->pdfPath)
                ->as("Invoice-{$invoiceNumber}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
