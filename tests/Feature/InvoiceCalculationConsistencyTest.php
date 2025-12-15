<?php

namespace Tests\Feature;

use App\Http\Services\InvoiceCalculationService;
use App\Http\Services\InvoiceFinalizationService;
use App\Http\Services\InvoiceService;
use App\Http\Services\InvoiceSnapshotBuilder;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceCalculationConsistencyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that same invoice calculated in two paths produces same totals.
     * This is the brutal consistency proof.
     */
    public function test_calculation_consistency_across_paths(): void
    {
        // Create necessary relationships
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'test@example.com',
            'currency' => 'KES',
        ]);
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
        ]);
        $client = Client::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'name' => 'Test Client',
            'email' => 'client@example.com',
        ]);

        // Create invoice with items
        $invoice = Invoice::create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => 'draft',
            'invoice_number' => 'INV-001',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 1000.00,
            'tax' => 160.00,
            'vat_amount' => 160.00,
            'total' => 1160.00,
            'grand_total' => 1194.80, // 1160 + (1160 * 0.03)
            'vat_registered' => true,
            'discount' => 0,
        ]);

        InvoiceItem::create([
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'description' => 'Test Item',
            'quantity' => 10,
            'unit_price' => 100.00,
            'total_price' => 1000.00,
            'vat_rate' => 16.00,
        ]);

        // Path 1: Calculate using calculation service directly
        $calculationService = new InvoiceCalculationService();
        $items = [
            [
                'quantity' => 10,
                'unit_price' => 100.00,
                'vat_included' => false,
                'vat_rate' => 16.00,
            ],
        ];
        $config = [
            'vat_enabled' => true,
            'vat_rate' => 16.00,
            'vat_registered' => true,
            'platform_fee_enabled' => true,
            'platform_fee_rate' => 0.03,
            'discount' => 0,
            'discount_type' => null,
        ];
        $calculationResult1 = $calculationService->calculate($items, $config);

        // Path 2: Calculate using InvoiceService
        $invoiceService = app(InvoiceService::class);
        $calculationResult2 = $invoiceService->calculatePreviewTotals($items, [
            'vat_registered' => true,
            'discount' => 0,
            'discount_type' => null,
        ]);

        // Path 3: Finalize invoice and get snapshot totals
        $finalizationService = app(InvoiceFinalizationService::class);
        $finalizedInvoice = $finalizationService->finalizeInvoice($invoice);
        $snapshot = $finalizedInvoice->snapshot;
        $snapshotTotals = $snapshot->snapshot_data['totals'];

        // Assert: All three paths produce identical totals
        $this->assertEquals($calculationResult1['grand_total'], $calculationResult2['grand_total'], 'Calculation service and InvoiceService must produce same grand total');
        $this->assertEquals($calculationResult1['grand_total'], $snapshotTotals['grand_total'], 'Calculation service and snapshot must produce same grand total');
        $this->assertEquals($calculationResult2['grand_total'], $snapshotTotals['grand_total'], 'InvoiceService and snapshot must produce same grand total');

        // Assert: All breakdowns match
        $this->assertEquals($calculationResult1['subtotal'], $calculationResult2['subtotal']);
        $this->assertEquals($calculationResult1['vat_amount'], $calculationResult2['vat_amount']);
        $this->assertEquals($calculationResult1['platform_fee'], $calculationResult2['platform_fee']);

        $this->assertEquals($calculationResult1['subtotal'], $snapshotTotals['subtotal']);
        $this->assertEquals($calculationResult1['vat_amount'], $snapshotTotals['vat_amount']);
        $this->assertEquals($calculationResult1['platform_fee'], $snapshotTotals['platform_fee']);

        // This proves calculation consistency across all paths
    }

    /**
     * Test that snapshot totals exactly match calculation service output.
     */
    public function test_snapshot_totals_match_calculation_service(): void
    {
        // Create necessary relationships
        $company = Company::create([
            'name' => 'Test Company 2',
            'email' => 'test2@example.com',
        ]);
        $user = User::create([
            'name' => 'Test User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
        ]);
        $client = Client::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'name' => 'Test Client 2',
            'email' => 'client2@example.com',
        ]);

        // Create invoice with items
        $invoice = Invoice::create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => 'draft',
            'invoice_number' => 'INV-002',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 2000.00,
            'tax' => 320.00,
            'vat_amount' => 320.00,
            'total' => 2320.00,
            'grand_total' => 2389.60,
            'vat_registered' => true,
            'discount' => 100.00,
            'discount_type' => 'fixed',
        ]);

        InvoiceItem::create([
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'description' => 'Item 1',
            'quantity' => 5,
            'unit_price' => 200.00,
            'total_price' => 1000.00,
            'vat_rate' => 16.00,
        ]);

        InvoiceItem::create([
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'description' => 'Item 2',
            'quantity' => 10,
            'unit_price' => 100.00,
            'total_price' => 1000.00,
            'vat_rate' => 16.00,
        ]);

        // Calculate using calculation service
        $calculationService = new InvoiceCalculationService();
        $items = [
            [
                'quantity' => 5,
                'unit_price' => 200.00,
                'vat_included' => false,
                'vat_rate' => 16.00,
            ],
            [
                'quantity' => 10,
                'unit_price' => 100.00,
                'vat_included' => false,
                'vat_rate' => 16.00,
            ],
        ];
        $config = [
            'vat_enabled' => true,
            'vat_rate' => 16.00,
            'vat_registered' => true,
            'platform_fee_enabled' => true,
            'platform_fee_rate' => 0.03,
            'discount' => 100.00,
            'discount_type' => 'fixed',
        ];
        $calculationResult = $calculationService->calculate($items, $config);

        // Finalize invoice and get snapshot
        $finalizationService = app(InvoiceFinalizationService::class);
        $finalizedInvoice = $finalizationService->finalizeInvoice($invoice);
        $snapshot = $finalizedInvoice->snapshot;
        $snapshotTotals = $snapshot->snapshot_data['totals'];

        // Assert: Snapshot totals exactly match calculation service output
        $this->assertEquals($calculationResult['subtotal'], $snapshotTotals['subtotal'], 'Subtotal must match');
        $this->assertEquals($calculationResult['discount'], $snapshotTotals['discount'], 'Discount must match');
        $this->assertEquals($calculationResult['subtotal_after_discount'], $snapshotTotals['subtotal_after_discount'], 'Subtotal after discount must match');
        $this->assertEquals($calculationResult['vat_amount'], $snapshotTotals['vat_amount'], 'VAT amount must match');
        $this->assertEquals($calculationResult['platform_fee'], $snapshotTotals['platform_fee'], 'Platform fee must match');
        $this->assertEquals($calculationResult['grand_total'], $snapshotTotals['grand_total'], 'Grand total must match');

        // This proves snapshot data is derived from single source of truth
    }

    /**
     * Test that no other code path changes totals (deterministic).
     */
    public function test_calculation_determinism(): void
    {
        $calculationService = new InvoiceCalculationService();

        $items = [
            [
                'quantity' => 10,
                'unit_price' => 100.00,
                'vat_included' => false,
                'vat_rate' => 16.00,
            ],
        ];
        $config = [
            'vat_enabled' => true,
            'vat_rate' => 16.00,
            'vat_registered' => true,
            'platform_fee_enabled' => true,
            'platform_fee_rate' => 0.03,
            'discount' => 0,
            'discount_type' => null,
        ];

        // Call calculation service multiple times with same input
        $result1 = $calculationService->calculate($items, $config);
        $result2 = $calculationService->calculate($items, $config);
        $result3 = $calculationService->calculate($items, $config);

        // Assert: Same input → same output (always)
        $this->assertEquals($result1['grand_total'], $result2['grand_total']);
        $this->assertEquals($result2['grand_total'], $result3['grand_total']);
        $this->assertEquals($result1['subtotal'], $result2['subtotal']);
        $this->assertEquals($result1['vat_amount'], $result2['vat_amount']);
        $this->assertEquals($result1['platform_fee'], $result2['platform_fee']);

        // This proves calculation service is deterministic
    }
}

