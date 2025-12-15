<?php

namespace Tests\Feature;

use App\Http\Services\InvoiceFinalizationService;
use App\Http\Services\InvoiceSnapshotBuilder;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceSnapshot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceSnapshotTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that snapshot can reproduce invoice without live database queries.
     * This proves snapshot independence.
     */
    public function test_snapshot_independence_from_live_data(): void
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

        // Create a draft invoice with items
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
            'grand_total' => 1160.00,
            'vat_registered' => true,
        ]);

        InvoiceItem::create([
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'description' => 'Test Item',
            'quantity' => 1,
            'unit_price' => 1000.00,
            'total_price' => 1000.00,
            'vat_rate' => 16.00,
        ]);

        // Finalize invoice with snapshot creation
        $finalizationService = new InvoiceFinalizationService(new InvoiceSnapshotBuilder());
        $finalizedInvoice = $finalizationService->finalizeInvoice($invoice);

        // Verify snapshot was created
        $this->assertNotNull($finalizedInvoice->snapshot);
        $this->assertEquals('finalized', $finalizedInvoice->status);

        // Get snapshot data
        $snapshot = $finalizedInvoice->snapshot;
        $snapshotData = $snapshot->snapshot_data;

        // Verify snapshot contains all necessary data
        $this->assertArrayHasKey('invoice', $snapshotData);
        $this->assertArrayHasKey('company', $snapshotData);
        $this->assertArrayHasKey('client', $snapshotData);
        $this->assertArrayHasKey('items', $snapshotData);
        $this->assertArrayHasKey('totals', $snapshotData);

        // Verify invoice data in snapshot
        $this->assertEquals('INV-001', $snapshotData['invoice']['invoice_number']);
        $this->assertEquals(1000.00, $snapshotData['totals']['subtotal']);
        $this->assertEquals(160.00, $snapshotData['totals']['vat_amount']);

        // Now simulate "deleting invoice from memory" by clearing relationships
        // and proving snapshot still has all data
        $invoice->unsetRelations();
        $company->delete();
        $client->delete();

        // Reload snapshot (should still work)
        $snapshot->refresh();
        $snapshotDataAfter = $snapshot->snapshot_data;

        // Verify snapshot data is still complete
        $this->assertEquals('INV-001', $snapshotDataAfter['invoice']['invoice_number']);
        $this->assertEquals('Test Company', $snapshotDataAfter['company']['name']);
        $this->assertEquals('Test Client', $snapshotDataAfter['client']['name']);
        $this->assertEquals(1000.00, $snapshotDataAfter['totals']['subtotal']);
        $this->assertCount(1, $snapshotDataAfter['items']);
        $this->assertEquals('Test Item', $snapshotDataAfter['items'][0]['description']);

        // This proves snapshot is independent of live data
    }

    /**
     * Test that finalized invoice must have snapshot (hard invariant).
     */
    public function test_finalized_invoice_must_have_snapshot(): void
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

        // Create and finalize invoice using service (ensures snapshot creation)
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
            'grand_total' => 2320.00,
        ]);

        $finalizationService = new InvoiceFinalizationService(new InvoiceSnapshotBuilder());
        $finalizedInvoice = $finalizationService->finalizeInvoice($invoice);

        // Verify snapshot exists
        $this->assertNotNull($finalizedInvoice->snapshot);
        $this->assertInstanceOf(InvoiceSnapshot::class, $finalizedInvoice->snapshot);

        // Verify snapshot cannot be deleted (would violate invariant)
        // Actually, we allow deletion but the invariant is: finalized invoice should have snapshot
        // This is enforced by the finalization service, not by preventing deletion
    }
}

