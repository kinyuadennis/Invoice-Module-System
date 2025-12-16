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
        public string $pdfPath
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $invoiceNumber = $this->invoice->invoice_number ?? 'INV-'.str_pad($this->invoice->id, 3, '0', STR_PAD_LEFT);
        $companyName = $this->invoice->company->name ?? 'InvoiceHub';

        return new Envelope(
            subject: "Invoice #{$invoiceNumber} from {$companyName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-sent',
            with: [
                'invoice' => $this->invoice,
                'client' => $this->invoice->client,
                'company' => $this->invoice->company,
                'invoiceNumber' => $this->invoice->invoice_number ?? 'INV-'.str_pad($this->invoice->id, 3, '0', STR_PAD_LEFT),
                'total' => $this->invoice->grand_total ?? $this->invoice->total,
                'dueDate' => $this->invoice->due_date,
            ],
        );
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
