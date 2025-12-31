<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Services\DataExportService;
use App\Http\Services\DataImportService;
use Illuminate\Http\Request;

class DataImportExportController extends Controller
{
    public function __construct(
        private DataExportService $exportService,
        private DataImportService $importService
    ) {}

    /**
     * Export clients to CSV.
     */
    public function exportClients()
    {
        return $this->exportService->exportClients();
    }

    /**
     * Export clients to Excel.
     */
    public function exportClientsExcel()
    {
        return $this->exportService->exportClientsExcel();
    }

    /**
     * Export invoices to CSV.
     */
    public function exportInvoices()
    {
        return $this->exportService->exportInvoices();
    }

    /**
     * Export invoices to Excel.
     */
    public function exportInvoicesExcel()
    {
        return $this->exportService->exportInvoicesExcel();
    }

    /**
     * Show import form.
     */
    public function showImportForm()
    {
        return view('user.data-import.show');
    }

    /**
     * Import clients from CSV.
     */
    public function importClients(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        try {
            $result = $this->importService->importClients($validated['file']);

            if ($result['success']) {
                $message = "Successfully imported {$result['imported']} client(s).";
                if ($result['skipped'] > 0) {
                    $message .= " {$result['skipped']} row(s) skipped.";
                }

                return redirect()->back()->with('success', $message)->with('import_errors', $result['errors']);
            }

            return redirect()->back()->withErrors(['error' => 'No clients were imported. Please check your file format.'])->with('import_errors', $result['errors']);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Import failed: '.$e->getMessage()]);
        }
    }

    /**
     * Download client import template.
     */
    public function downloadClientTemplate()
    {
        return $this->importService->getClientImportTemplate();
    }
}
