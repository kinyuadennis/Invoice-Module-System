<?php

namespace App\Services;

use App\Models\InvoiceSnapshot;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfInvoiceRenderer
{
    /**
     * Render the PDF from the invoice snapshot.
     *
     * @param InvoiceSnapshot $snapshot
     * @return string
     */
    public function render(InvoiceSnapshot $snapshot): string
    {
        // STRICT RULE: Only use data from the snapshot. No DB queries allowed here.
        $data = $snapshot->snapshot_data;

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $data['invoice_details'],
            'seller' => $data['seller_details'],
            'client' => $data['client_details'],
            'items' => $data['items'],
            'totals' => $data['totals'],
            'compliance' => $data['compliance'],
            'metadata' => $data['metadata'],
        ]);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable-local-file-access', true);

        return $pdf->output();
    }
}
