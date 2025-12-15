<?php

namespace Tests\Feature\Api;

use App\Http\Services\InvoiceFinalizationService;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function getAuthToken(User $user): string
    {
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        return $loginResponse->json('data.token');
    }

    /**
     * Test listing invoices requires authentication.
     */
    public function test_listing_invoices_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/invoices');

        $response->assertStatus(401);
    }

    /**
     * Test listing invoices returns company-scoped results.
     */
    public function test_listing_invoices_returns_company_scoped_results(): void
    {
        $company1 = Company::create([
            'name' => 'Company 1',
            'email' => 'company1@example.com',
        ]);
        $company2 = Company::create([
            'name' => 'Company 2',
            'email' => 'company2@example.com',
        ]);

        $user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
            'company_id' => $company1->id,
        ]);
        $user2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
            'company_id' => $company2->id,
        ]);

        $client1 = Client::create([
            'company_id' => $company1->id,
            'user_id' => $user1->id,
            'name' => 'Client 1',
        ]);

        $invoice1 = Invoice::create([
            'company_id' => $company1->id,
            'client_id' => $client1->id,
            'user_id' => $user1->id,
            'status' => 'draft',
            'invoice_number' => 'INV-001',
            'subtotal' => 100.00,
            'grand_total' => 100.00,
        ]);

        $invoice2 = Invoice::create([
            'company_id' => $company2->id,
            'user_id' => $user2->id,
            'status' => 'draft',
            'invoice_number' => 'INV-002',
            'subtotal' => 200.00,
            'grand_total' => 200.00,
        ]);

        $token = $this->getAuthToken($user1);

        $response = $this->getJson('/api/v1/invoices', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($invoice1->id, $data[0]['id']);
    }

    /**
     * Test creating invoice requires authentication.
     */
    public function test_creating_invoice_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/invoices', [
            'items' => [
                [
                    'description' => 'Test Item',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                ],
            ],
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test creating invoice creates draft invoice.
     */
    public function test_creating_invoice_creates_draft_invoice(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'company@example.com',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'company_id' => $company->id,
        ]);

        $client = Client::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'name' => 'Test Client',
        ]);

        $token = $this->getAuthToken($user);

        $response = $this->postJson('/api/v1/invoices', [
            'client_id' => $client->id,
            'items' => [
                [
                    'description' => 'Test Item',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                ],
            ],
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'status',
                'invoice_number',
            ],
        ]);

        $this->assertEquals('draft', $response->json('data.status'));
    }

    /**
     * Test updating invoice only works for draft invoices.
     */
    public function test_updating_invoice_only_works_for_draft_invoices(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'company@example.com',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
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
            'invoice_number' => 'INV-001',
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

        // Finalize invoice
        $finalizationService = app(InvoiceFinalizationService::class);
        $finalizedInvoice = $finalizationService->finalizeInvoice($invoice);

        $token = $this->getAuthToken($user);

        // Try to update finalized invoice
        $response = $this->patchJson("/api/v1/invoices/{$finalizedInvoice->id}", [
            'notes' => 'Updated notes',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'status' => 'error',
        ]);
    }

    /**
     * Test finalizing invoice creates snapshot.
     */
    public function test_finalizing_invoice_creates_snapshot(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'company@example.com',
            'kra_pin' => 'P051234567A',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
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
            'invoice_number' => 'INV-001',
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

        $token = $this->getAuthToken($user);

        $response = $this->postJson("/api/v1/invoices/{$invoice->id}/finalize", [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200);
        $this->assertEquals('finalized', $response->json('data.status'));
        $this->assertTrue($response->json('data.has_snapshot'));
    }

    /**
     * Test ETIMS export requires finalized invoice.
     */
    public function test_etims_export_requires_finalized_invoice(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'company@example.com',
            'kra_pin' => 'P051234567A',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
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
            'invoice_number' => 'INV-001',
            'subtotal' => 100.00,
            'grand_total' => 100.00,
        ]);

        $token = $this->getAuthToken($user);

        $response = $this->getJson("/api/v1/invoices/{$invoice->id}/export/etims", [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Invoice must be finalized to export for ETIMS.',
        ]);
    }

    /**
     * Test ETIMS export returns valid JSON structure.
     */
    public function test_etims_export_returns_valid_json_structure(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'company@example.com',
            'kra_pin' => 'P051234567A',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
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
            'invoice_number' => 'INV-001',
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

        // Finalize invoice
        $finalizationService = app(InvoiceFinalizationService::class);
        $finalizedInvoice = $finalizationService->finalizeInvoice($invoice);

        $token = $this->getAuthToken($user);

        $response = $this->getJson("/api/v1/invoices/{$finalizedInvoice->id}/export/etims", [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'invoiceNumber',
                'issueDate',
                'seller' => [
                    'kraPin',
                    'name',
                ],
                'buyer' => [
                    'kraPin',
                    'name',
                ],
                'items',
                'totals',
            ],
        ]);
    }

    /**
     * Test cross-company access is forbidden.
     */
    public function test_cross_company_access_is_forbidden(): void
    {
        $company1 = Company::create([
            'name' => 'Company 1',
            'email' => 'company1@example.com',
        ]);
        $company2 = Company::create([
            'name' => 'Company 2',
            'email' => 'company2@example.com',
        ]);

        $user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
            'company_id' => $company1->id,
        ]);

        $client2 = Client::create([
            'company_id' => $company2->id,
            'user_id' => null,
            'name' => 'Client 2',
        ]);

        $invoice2 = Invoice::create([
            'company_id' => $company2->id,
            'client_id' => $client2->id,
            'user_id' => null,
            'status' => 'draft',
            'invoice_number' => 'INV-002',
            'subtotal' => 200.00,
            'grand_total' => 200.00,
        ]);

        $token = $this->getAuthToken($user1);

        // User 1 tries to access Company 2's invoice
        $response = $this->getJson("/api/v1/invoices/{$invoice2->id}", [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(404); // Not found (company scoping)
    }
}
