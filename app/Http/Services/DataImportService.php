<?php

namespace App\Http\Services;

use App\Models\Client;
use App\Services\CurrentCompanyService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DataImportService
{
    /**
     * Import clients from CSV file.
     *
     * @return array<string, mixed>
     */
    public function importClients(UploadedFile $file, ?int $companyId = null): array
    {
        $companyId = $companyId ?? CurrentCompanyService::requireId();

        $data = [];
        $errors = [];
        $imported = 0;
        $skipped = 0;

        // Read CSV file
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            throw new \RuntimeException('Unable to read CSV file');
        }

        // Read header row
        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            throw new \RuntimeException('Invalid CSV file format');
        }

        // Normalize headers (trim and lowercase)
        $headers = array_map(function ($header) {
            return strtolower(trim($header));
        }, $headers);

        $lineNumber = 1;

        // Read data rows
        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if (count($row) !== count($headers)) {
                $errors[] = "Line {$lineNumber}: Column count mismatch";
                $skipped++;

                continue;
            }

            // Map row data to associative array
            $rowData = array_combine($headers, $row);

            // Map common column names
            $clientData = [
                'name' => $rowData['name'] ?? $rowData['client name'] ?? $rowData['company name'] ?? null,
                'email' => $rowData['email'] ?? null,
                'phone' => $rowData['phone'] ?? $rowData['phone number'] ?? null,
                'address' => $rowData['address'] ?? null,
                'kra_pin' => $rowData['kra pin'] ?? $rowData['kra_pin'] ?? $rowData['tax id'] ?? null,
            ];

            // Validate required fields
            $validator = Validator::make($clientData, [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:clients,email,NULL,id,company_id,'.$companyId,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'kra_pin' => 'nullable|string|max:11',
            ]);

            if ($validator->fails()) {
                $errors[] = "Line {$lineNumber}: ".implode(', ', $validator->errors()->all());
                $skipped++;

                continue;
            }

            // Check if client already exists (by email or name)
            $existing = Client::where('company_id', $companyId)
                ->where(function ($query) use ($clientData) {
                    if (! empty($clientData['email'])) {
                        $query->where('email', $clientData['email']);
                    } else {
                        $query->where('name', $clientData['name']);
                    }
                })
                ->first();

            if ($existing) {
                $errors[] = "Line {$lineNumber}: Client already exists (".$clientData['name'].')';
                $skipped++;

                continue;
            }

            try {
                DB::beginTransaction();

                // Normalize KRA PIN to uppercase
                if (! empty($clientData['kra_pin'])) {
                    $clientData['kra_pin'] = strtoupper($clientData['kra_pin']);
                }

                // Normalize phone number if service is available
                if (! empty($clientData['phone']) && app()->bound(\App\Services\PhoneNumberService::class)) {
                    try {
                        $phoneService = app(\App\Services\PhoneNumberService::class);
                        $clientData['phone'] = $phoneService->normalize($clientData['phone']);
                    } catch (\Exception $e) {
                        // Continue without normalization if service fails
                    }
                }

                $clientData['company_id'] = $companyId;
                $clientData['user_id'] = auth()->id();

                Client::create($clientData);

                DB::commit();
                $imported++;
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = "Line {$lineNumber}: Error creating client - ".$e->getMessage();
                $skipped++;
            }
        }

        fclose($handle);

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'success' => $imported > 0,
        ];
    }

    /**
     * Get sample CSV template for client import.
     */
    public function getClientImportTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'client_import_template.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Name',
                'Email',
                'Phone',
                'Address',
                'KRA PIN',
            ]);

            // Add sample row
            fputcsv($file, [
                'John Doe',
                'john@example.com',
                '+254712345678',
                '123 Main Street, Nairobi',
                'P123456789A',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
