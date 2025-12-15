<?php

namespace Tests\Feature;

use App\Http\Services\InvoiceAuditService;
use App\Http\Services\InvoiceFinalizationService;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceAuditLog;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test audit log is created when invoice is created.
     */
    public function test_audit_log_created_on_invoice_creation(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'company@example.com',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
        ]);

        $this->actingAs($user);

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
            'subtotal' => 1000.00,
            'grand_total' => 1000.00,
        ]);

        // Manually trigger audit log (normally done in service)
        $auditService = app(InvoiceAuditService::class);
        $auditService->logCreate($invoice);

        $this->assertDatabaseHas('invoice_audit_logs', [
            'invoice_id' => $invoice->id,
            'action_type' => InvoiceAuditLog::ACTION_CREATE,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test audit log is created when invoice is finalized.
     */
    public function test_audit_log_created_on_invoice_finalization(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'company@example.com',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
        ]);

        $this->actingAs($user);

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
            'invoice_number' => 'INV-002',
            'subtotal' => 1000.00,
            'grand_total' => 1000.00,
        ]);

        InvoiceItem::create([
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'description' => 'Item',
            'quantity' => 1,
            'unit_price' => 1000.00,
            'total_price' => 1000.00,
        ]);

        // Finalize invoice
        $finalizationService = app(InvoiceFinalizationService::class);
        $finalizationService->finalizeInvoice($invoice);

        $this->assertDatabaseHas('invoice_audit_logs', [
            'invoice_id' => $invoice->id,
            'action_type' => InvoiceAuditLog::ACTION_FINALIZE,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test audit log includes IP address and source.
     */
    public function test_audit_log_includes_context(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'company@example.com',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
        ]);

        $this->actingAs($user);

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
            'subtotal' => 1000.00,
            'grand_total' => 1000.00,
        ]);

        // Create audit log with request context
        $request = \Illuminate\Http\Request::create('/test', 'GET');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $request->headers->set('User-Agent', 'Test Agent');

        $auditService = app(InvoiceAuditService::class);
        $log = $auditService->logPdfGenerate($invoice, $request);

        $this->assertEquals('127.0.0.1', $log->ip_address);
        $this->assertEquals('Test Agent', $log->user_agent);
        $this->assertEquals(InvoiceAuditLog::SOURCE_UI, $log->source);
    }

    /**
     * Test audit log API endpoint returns logs.
     */
    public function test_audit_log_api_endpoint_returns_logs(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'company@example.com',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

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
            'invoice_number' => 'INV-004',
            'subtotal' => 1000.00,
            'grand_total' => 1000.00,
        ]);

        // Create audit log
        $auditService = app(InvoiceAuditService::class);
        $auditService->logCreate($invoice);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/invoices/{$invoice->id}/audit-logs");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'invoice_id',
                    'action_type',
                    'created_at',
                ],
            ],
        ]);
    }

    /**
     * Test cleanup job deletes old audit logs.
     */
    public function test_cleanup_job_deletes_old_audit_logs(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'company@example.com',
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
            'invoice_number' => 'INV-005',
            'subtotal' => 1000.00,
            'grand_total' => 1000.00,
        ]);

        // Create old audit log (25 months ago)
        $oldLog = InvoiceAuditLog::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'action_type' => InvoiceAuditLog::ACTION_CREATE,
            'created_at' => now()->subMonths(25),
        ]);

        // Create recent audit log (1 month ago)
        $recentLog = InvoiceAuditLog::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'action_type' => InvoiceAuditLog::ACTION_UPDATE,
            'created_at' => now()->subMonth(),
        ]);

        // Run cleanup job (24 months retention)
        $job = new \App\Jobs\CleanupOldAuditLogs(24);
        $job->handle();

        // Old log should be deleted
        $this->assertDatabaseMissing('invoice_audit_logs', [
            'id' => $oldLog->id,
        ]);

        // Recent log should remain
        $this->assertDatabaseHas('invoice_audit_logs', [
            'id' => $recentLog->id,
        ]);
    }
}
