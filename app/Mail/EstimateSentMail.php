<?php

namespace App\Mail;

use App\Models\Estimate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EstimateSentMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Estimate $estimate,
        public ?string $pdfPath = null
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $estimateNumber = $this->estimate->full_number ?? $this->estimate->estimate_reference ?? 'EST-'.str_pad($this->estimate->id, 3, '0', STR_PAD_LEFT);
        $company = $this->estimate->company;
        $companyName = $company->name ?? 'InvoiceHub';

        $subject = "Estimate #{$estimateNumber} from {$companyName}";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $company = $this->estimate->company;
        $estimateNumber = $this->estimate->full_number ?? $this->estimate->estimate_reference ?? 'EST-'.str_pad($this->estimate->id, 3, '0', STR_PAD_LEFT);
        $total = $this->estimate->grand_total ?? 0;
        $expiryDate = $this->estimate->expiry_date;

        return new Content(
            view: 'emails.estimate-sent',
            with: [
                'estimate' => $this->estimate,
                'client' => $this->estimate->client,
                'company' => $company,
                'estimateNumber' => $estimateNumber,
                'total' => $total,
                'expiryDate' => $expiryDate,
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
        if (! $this->pdfPath || ! file_exists($this->pdfPath)) {
            return [];
        }

        $estimateNumber = $this->estimate->full_number ?? $this->estimate->estimate_reference ?? 'EST-'.str_pad($this->estimate->id, 3, '0', STR_PAD_LEFT);

        return [
            Attachment::fromPath($this->pdfPath)
                ->as("Estimate-{$estimateNumber}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
