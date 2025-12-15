<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that finalized invoices cannot be modified.
     * This is the brutal proof that immutability works.
     */
    public function test_finalized_invoice_cannot_be_modified(): void
    {
        // Create necessary relationships
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'test@example.com',
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
        ]);

        // Create invoice items
        InvoiceItem::create([
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'description' => 'Test Item',
            'quantity' => 1,
            'unit_price' => 1000.00,
            'total_price' => 1000.00,
        ]);

        // Verify invoice is draft
        $this->assertTrue($invoice->isDraft());
        $this->assertTrue($invoice->isMutable());

        // Finalize the invoice
        $invoice->finalize();
        $invoice->refresh();

        // Verify invoice is finalized
        $this->assertEquals('finalized', $invoice->status);
        $this->assertTrue($invoice->isFinalized());
        $this->assertFalse($invoice->isMutable());

        // Attempt to modify financial field - should throw exception
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot modify finalized invoice');
        $invoice->subtotal = 999.00;
        $invoice->save();

        // Reset for next assertion
        $invoice->refresh();

        // Attempt to modify structural field - should throw exception
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot modify finalized invoice');
        $invoice->client_id = $client->id + 1;
        $invoice->save();

        // Reset for next assertion
        $invoice->refresh();

        // Attempt to modify invoice number - should throw exception
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot modify finalized invoice');
        $invoice->invoice_number = 'INV-002';
        $invoice->save();
    }

    /**
     * Test that invoice items cannot be modified on finalized invoices.
     */
    public function test_finalized_invoice_items_cannot_be_modified(): void
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

        // Create a draft invoice with items
        $invoice = Invoice::create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => 'draft',
            'invoice_number' => 'INV-002',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 1000.00,
            'tax' => 160.00,
            'vat_amount' => 160.00,
            'total' => 1160.00,
            'grand_total' => 1160.00,
        ]);

        $invoiceItem = InvoiceItem::create([
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'description' => 'Test Item',
            'quantity' => 1,
            'unit_price' => 1000.00,
            'total_price' => 1000.00,
        ]);

        // Finalize the invoice
        $invoice->finalize();
        $invoice->refresh();

        // Attempt to modify invoice item - should throw exception
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot modify invoice items on finalized invoice');
        $invoiceItem->quantity = 2;
        $invoiceItem->save();

        // Reset for next assertion
        $invoiceItem->refresh();

        // Attempt to delete invoice item - should throw exception
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot delete invoice items on finalized invoice');
        $invoiceItem->delete();
    }

    /**
     * Test that finalized invoices cannot be deleted.
     */
    public function test_finalized_invoice_cannot_be_deleted(): void
    {
        // Create necessary relationships
        $company = Company::create([
            'name' => 'Test Company 3',
            'email' => 'test3@example.com',
        ]);
        $user = User::create([
            'name' => 'Test User 3',
            'email' => 'user3@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
        ]);
        $client = Client::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'name' => 'Test Client 3',
            'email' => 'client3@example.com',
        ]);

        // Create a draft invoice
        $invoice = Invoice::create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => 'draft',
            'invoice_number' => 'INV-003',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 1000.00,
            'tax' => 160.00,
            'vat_amount' => 160.00,
            'total' => 1160.00,
            'grand_total' => 1160.00,
        ]);

        // Finalize the invoice
        $invoice->finalize();

        // Attempt to delete finalized invoice - should throw exception
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot delete finalized invoice');
        $invoice->delete();
    }

    /**
     * Test that finalize() validates prerequisites.
     */
    public function test_finalize_validates_prerequisites(): void
    {
        // Create necessary relationships
        $company = Company::create([
            'name' => 'Test Company 4',
            'email' => 'test4@example.com',
        ]);
        $user = User::create([
            'name' => 'Test User 4',
            'email' => 'user4@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
        ]);
        $client = Client::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'name' => 'Test Client 4',
            'email' => 'client4@example.com',
        ]);

        // Test: Cannot finalize without invoice_number
        $invoice = Invoice::create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => 'draft',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 1000.00,
            'tax' => 160.00,
            'vat_amount' => 160.00,
            'total' => 1160.00,
            'grand_total' => 1160.00,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Invoice cannot be finalized without an invoice number');
        $invoice->finalize();

        // Test: Cannot finalize without client_id
        $invoice2 = Invoice::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'status' => 'draft',
            'invoice_number' => 'INV-004',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 1000.00,
            'tax' => 160.00,
            'vat_amount' => 160.00,
            'total' => 1160.00,
            'grand_total' => 1160.00,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Invoice cannot be finalized without a client');
        $invoice2->finalize();

        // Test: Cannot finalize non-draft invoice
        $invoice3 = Invoice::create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => 'sent',
            'invoice_number' => 'INV-005',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 1000.00,
            'tax' => 160.00,
            'vat_amount' => 160.00,
            'total' => 1160.00,
            'grand_total' => 1160.00,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('cannot be finalized');
        $invoice3->finalize();
    }

    /**
     * Test that status transitions are validated on finalized invoices.
     */
    public function test_finalized_invoice_status_transitions_are_validated(): void
    {
        // Create necessary relationships
        $company = Company::create([
            'name' => 'Test Company 5',
            'email' => 'test5@example.com',
        ]);
        $user = User::create([
            'name' => 'Test User 5',
            'email' => 'user5@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
        ]);
        $client = Client::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'name' => 'Test Client 5',
            'email' => 'client5@example.com',
        ]);

        // Create and finalize invoice
        $invoice = Invoice::create([
            'company_id' => $company->id,
            'client_id' => $client->id,
            'user_id' => $user->id,
            'status' => 'draft',
            'invoice_number' => 'INV-006',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 1000.00,
            'tax' => 160.00,
            'vat_amount' => 160.00,
            'total' => 1160.00,
            'grand_total' => 1160.00,
        ]);

        $invoice->finalize();
        $invoice->refresh();

        // Test: Can transition from finalized to sent
        $invoice->status = 'sent';
        $invoice->save();
        $this->assertEquals('sent', $invoice->status);

        // Test: Cannot transition from sent back to draft
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Invalid status transition');
        $invoice->status = 'draft';
        $invoice->save();
    }
}
