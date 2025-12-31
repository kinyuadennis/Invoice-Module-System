<?php

namespace App\Http\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Services\CurrentCompanyService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataExportService
{
    /**
     * Export clients to CSV.
     */
    public function exportClients(?int $companyId = null): StreamedResponse
    {
        $companyId = $companyId ?? CurrentCompanyService::requireId();

        $clients = Client::where('company_id', $companyId)
            ->with(['company'])
            ->get();

        $filename = 'clients_'.now()->format('Y-m-d_H-i-s').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($clients) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'ID',
                'Name',
                'Email',
                'Phone',
                'Address',
                'KRA PIN',
                'Company',
                'Created At',
                'Updated At',
            ]);

            // Add data rows
            foreach ($clients as $client) {
                fputcsv($file, [
                    $client->id,
                    $client->name,
                    $client->email,
                    $client->phone,
                    $client->address,
                    $client->kra_pin,
                    $client->company->name ?? '',
                    $client->created_at,
                    $client->updated_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export invoices to CSV.
     */
    public function exportInvoices(?int $companyId = null): StreamedResponse
    {
        $companyId = $companyId ?? CurrentCompanyService::requireId();

        $invoices = Invoice::where('company_id', $companyId)
            ->with(['client', 'company', 'user'])
            ->get();

        $filename = 'invoices_'.now()->format('Y-m-d_H-i-s').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'ID',
                'Invoice Number',
                'Client Name',
                'Client Email',
                'Status',
                'Issue Date',
                'Due Date',
                'Subtotal',
                'Tax',
                'VAT Amount',
                'Discount',
                'Total',
                'Grand Total',
                'Payment Method',
                'Created By',
                'Created At',
                'Updated At',
            ]);

            // Add data rows
            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->id,
                    $invoice->invoice_number ?? $invoice->invoice_reference,
                    $invoice->client->name ?? '',
                    $invoice->client->email ?? '',
                    $invoice->status,
                    $invoice->issue_date,
                    $invoice->due_date,
                    $invoice->subtotal,
                    $invoice->tax,
                    $invoice->vat_amount,
                    $invoice->discount,
                    $invoice->total,
                    $invoice->grand_total,
                    $invoice->payment_method,
                    $invoice->user->name ?? '',
                    $invoice->created_at,
                    $invoice->updated_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export invoices to Excel (CSV format with .xls extension for compatibility).
     */
    public function exportInvoicesExcel(?int $companyId = null): StreamedResponse
    {
        // For now, use CSV format but with .xls extension
        // In production, consider installing maatwebsite/excel for proper Excel support
        $response = $this->exportInvoices($companyId);
        $filename = 'invoices_'.now()->format('Y-m-d_H-i-s').'.xls';

        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }

    /**
     * Export clients to Excel (CSV format with .xls extension for compatibility).
     */
    public function exportClientsExcel(?int $companyId = null): StreamedResponse
    {
        // For now, use CSV format but with .xls extension
        // In production, consider installing maatwebsite/excel for proper Excel support
        $response = $this->exportClients($companyId);
        $filename = 'clients_'.now()->format('Y-m-d_H-i-s').'.xls';

        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }
}
