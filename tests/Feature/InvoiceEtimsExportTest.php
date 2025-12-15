<?php

namespace Tests\Feature;

use App\Http\Services\InvoiceFinalizationService;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceEtimsExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test ETIMS export requires finalized invoice with snapshot.
     */
    public function test_etims_export_requires_finalized_invoice(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'test@example.com',
            'kra_pin' => 'P051234567A',
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
            'kra_pin' => 'P052345678B',
        ]);

        // Create draft invoice (not finalized)
        $invoice = Invoice::create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => 'draft',
            'invoice_number' => 'INV-001',
            'issue_date' => now(),
            'subtotal' => 1000.00,
            'vat_amount' => 160.00,
            'grand_total' => 1160.00,
            'vat_registered' => true,
        ]);

        $this->actingAs($user);

        // Attempt to export draft invoice
        $response = $this->get(route('user.invoices.export-etims', $invoice->id));

        $response->assertStatus(404);
        $response->assertSee('Invoice must be finalized');
    }

    /**
     * Test ETIMS export generates valid JSON structure.
     */
    public function test_etims_export_generates_valid_json(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'test@example.com',
            'kra_pin' => 'P051234567A',
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
            'kra_pin' => 'P052345678B',
        ]);

        // Create and finalize invoice
        $invoice = Invoice::create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => 'draft',
            'invoice_number' => 'INV-001',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 1000.00,
            'vat_amount' => 160.00,
            'grand_total' => 1160.00,
            'vat_registered' => true,
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

        // Finalize invoice and create snapshot
        $finalizationService = app(InvoiceFinalizationService::class);
        $finalizedInvoice = $finalizationService->finalizeInvoice($invoice);

        $this->actingAs($user);

        // Export to ETIMS
        $response = $this->get(route('user.invoices.export-etims', $finalizedInvoice->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertHeader('Content-Disposition');

        $jsonData = json_decode($response->getContent(), true);

        // Verify ETIMS structure
        $this->assertArrayHasKey('invoiceNumber', $jsonData);
        $this->assertArrayHasKey('issueDate', $jsonData);
        $this->assertArrayHasKey('seller', $jsonData);
        $this->assertArrayHasKey('buyer', $jsonData);
        $this->assertArrayHasKey('items', $jsonData);
        $this->assertArrayHasKey('totals', $jsonData);

        // Verify seller (company) data
        $this->assertEquals('P051234567A', $jsonData['seller']['kraPin']);
        $this->assertEquals('Test Company', $jsonData['seller']['name']);

        // Verify buyer (client) data
        $this->assertEquals('P052345678B', $jsonData['buyer']['kraPin']);
        $this->assertEquals('Test Client', $jsonData['buyer']['name']);

        // Verify totals
        $this->assertEquals(1000.00, $jsonData['totals']['subtotal']);
        $this->assertEquals(160.00, $jsonData['totals']['vatAmount']);
        $this->assertEquals(1160.00, $jsonData['totals']['total']);
    }

    /**
     * Test ETIMS export includes all required fields.
     */
    public function test_etims_export_includes_required_fields(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'test@example.com',
            'kra_pin' => 'P051234567A',
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
            'kra_pin' => 'P052345678B',
        ]);

        $invoice = Invoice::create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => 'draft',
            'invoice_number' => 'INV-002',
            'issue_date' => now(),
            'subtotal' => 500.00,
            'vat_amount' => 80.00,
            'grand_total' => 580.00,
            'vat_registered' => true,
        ]);

        InvoiceItem::create([
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'description' => 'Item 1',
            'quantity' => 5,
            'unit_price' => 100.00,
            'total_price' => 500.00,
            'vat_rate' => 16.00,
        ]);

        $finalizationService = app(InvoiceFinalizationService::class);
        $finalizedInvoice = $finalizationService->finalizeInvoice($invoice);

        $this->actingAs($user);

        $response = $this->get(route('user.invoices.export-etims', $finalizedInvoice->id));
        $jsonData = json_decode($response->getContent(), true);

        // Required fields check
        $this->assertNotEmpty($jsonData['invoiceNumber']);
        $this->assertNotEmpty($jsonData['issueDate']);
        $this->assertNotEmpty($jsonData['seller']['kraPin']);
        $this->assertNotEmpty($jsonData['seller']['name']);
        $this->assertNotEmpty($jsonData['totals']['subtotal']);
        $this->assertNotEmpty($jsonData['totals']['total']);
        $this->assertIsArray($jsonData['items']);
        $this->assertNotEmpty($jsonData['items']);
    }

    /**
     * Test ETIMS export fails when required fields are missing.
     */
    public function test_etims_export_fails_with_missing_required_fields(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'test@example.com',
            // Missing KRA PIN - required for ETIMS
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
        ]);

        $invoice = Invoice::create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => 'draft',
            'invoice_number' => 'INV-003',
            'issue_date' => now(),
            'subtotal' => 100.00,
            'grand_total' => 100.00,
        ]);

        InvoiceItem::create([
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'description' => 'Item',
            'quantity' => 1,
            'unit_price' => 100.00,
            'total_price' => 100.00,
        ]);

        $finalizationService = app(InvoiceFinalizationService::class);
        $finalizedInvoice = $finalizationService->finalizeInvoice($invoice);

        $this->actingAs($user);

        // Export should fail due to missing company KRA PIN
        $response = $this->get(route('user.invoices.export-etims', $finalizedInvoice->id));

        $response->assertStatus(302); // Redirect with error
        $response->assertSessionHas('error');
    }

    /**
     * Test ETIMS export is company-scoped.
     */
    public function test_etims_export_is_company_scoped(): void
    {
        $company1 = Company::create([
            'name' => 'Company 1',
            'email' => 'company1@example.com',
            'kra_pin' => 'P051234567A',
        ]);
        $company2 = Company::create([
            'name' => 'Company 2',
            'email' => 'company2@example.com',
            'kra_pin' => 'P052345678B',
        ]);

        $user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company1->id,
        ]);
        $user2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company2->id,
        ]);

        $client1 = Client::create([
            'company_id' => $company1->id,
            'user_id' => $user1->id,
            'name' => 'Client 1',
            'kra_pin' => 'P053456789C',
        ]);

        $invoice = Invoice::create([
            'company_id' => $company1->id,
            'client_id' => $client1->id,
            'user_id' => $user1->id,
            'status' => 'draft',
            'invoice_number' => 'INV-004',
            'issue_date' => now(),
            'subtotal' => 100.00,
            'grand_total' => 100.00,
        ]);

        InvoiceItem::create([
            'company_id' => $company1->id,
            'invoice_id' => $invoice->id,
            'description' => 'Item',
            'quantity' => 1,
            'unit_price' => 100.00,
            'total_price' => 100.00,
        ]);

        $finalizationService = app(InvoiceFinalizationService::class);
        $finalizedInvoice = $finalizationService->finalizeInvoice($invoice);

        // User 2 (different company) tries to export User 1's invoice
        $this->actingAs($user2);

        $response = $this->get(route('user.invoices.export-etims', $finalizedInvoice->id));

        $response->assertStatus(403); // Forbidden - company scoping works
    }

    /**
     * Test ETIMS XML export generates valid XML.
     */
    public function test_etims_xml_export_generates_valid_xml(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'test@example.com',
            'kra_pin' => 'P051234567A',
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
            'kra_pin' => 'P052345678B',
        ]);

        $invoice = Invoice::create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => 'draft',
            'invoice_number' => 'INV-005',
            'issue_date' => now(),
            'subtotal' => 100.00,
            'grand_total' => 100.00,
        ]);

        InvoiceItem::create([
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'description' => 'Item',
            'quantity' => 1,
            'unit_price' => 100.00,
            'total_price' => 100.00,
        ]);

        $finalizationService = app(InvoiceFinalizationService::class);
        $finalizedInvoice = $finalizationService->finalizeInvoice($invoice);

        $this->actingAs($user);

        $response = $this->get(route('user.invoices.export-etims-xml', $finalizedInvoice->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertHeader('Content-Disposition');

        $xmlContent = $response->getContent();
        $this->assertStringContainsString('<?xml', $xmlContent);
        $this->assertStringContainsString('<invoice>', $xmlContent);
    }
}
