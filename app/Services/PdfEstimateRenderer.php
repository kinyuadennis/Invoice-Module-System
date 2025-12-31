<?php

namespace App\Services;

use App\Models\Estimate;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfEstimateRenderer
{
    /**
     * Render the PDF for an estimate.
     */
    public function render(Estimate $estimate): string
    {
        $estimate->load(['client', 'company', 'items']);

        // Prepare data for PDF view
        $data = [
            'estimate' => [
                'full_number' => $estimate->full_number ?? $estimate->estimate_number ?? $estimate->estimate_reference,
                'estimate_number' => $estimate->estimate_number ?? $estimate->estimate_reference,
                'estimate_reference' => $estimate->estimate_reference,
                'issue_date' => $estimate->issue_date,
                'expiry_date' => $estimate->expiry_date,
                'po_number' => $estimate->po_number,
                'notes' => $estimate->notes,
                'terms_and_conditions' => $estimate->terms_and_conditions,
                'status' => $estimate->status,
            ],
            'seller' => [
                'name' => $estimate->company->name,
                'address' => $estimate->company->address,
                'email' => $estimate->company->email,
                'phone' => $estimate->company->phone,
                'kra_pin' => $estimate->company->kra_pin,
                'logo' => $estimate->company->logo ? asset('storage/'.$estimate->company->logo) : null,
            ],
            'client' => $estimate->client ? [
                'name' => $estimate->client->name,
                'email' => $estimate->client->email,
                'phone' => $estimate->client->phone,
                'address' => $estimate->client->address,
                'kra_pin' => $estimate->client->kra_pin,
            ] : null,
            'items' => $estimate->items->map(function ($item) {
                return [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'vat_included' => $item->vat_included,
                    'vat_rate' => $item->vat_rate,
                ];
            })->toArray(),
            'totals' => [
                'subtotal' => $estimate->subtotal,
                'discount' => $estimate->discount,
                'discount_type' => $estimate->discount_type,
                'vat_amount' => $estimate->vat_amount,
                'platform_fee' => $estimate->platform_fee,
                'grand_total' => $estimate->grand_total,
                'vat_registered' => $estimate->vat_registered,
            ],
        ];

        $pdf = Pdf::loadView('pdf.estimate', $data);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable-local-file-access', true);

        return $pdf->output();
    }
}
