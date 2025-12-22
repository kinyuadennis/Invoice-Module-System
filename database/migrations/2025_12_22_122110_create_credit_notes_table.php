<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('invoice_templates')->onDelete('set null');
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade'); // Original invoice
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Credit note numbering
            $table->string('credit_note_reference')->nullable();
            $table->string('prefix_used')->nullable();
            $table->unsignedInteger('serial_number')->nullable();
            $table->string('credit_note_number')->nullable();
            $table->string('full_number')->nullable();

            // Status: draft, issued, applied, cancelled
            $table->enum('status', ['draft', 'issued', 'applied', 'cancelled'])->default('draft');

            // Reason for credit note
            $table->enum('reason', ['refund', 'adjustment', 'error', 'cancellation', 'other'])->default('other');
            $table->text('reason_details')->nullable();

            // Dates
            $table->date('issue_date');
            $table->date('applied_date')->nullable(); // When credit was applied to invoice

            // Financials
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('platform_fee', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0); // Total credit amount

            // eTIMS reversal fields
            $table->string('etims_control_number')->nullable();
            $table->string('etims_qr_code')->nullable();
            $table->timestamp('etims_submitted_at')->nullable();
            $table->json('etims_metadata')->nullable();
            $table->string('etims_reversal_reference')->nullable(); // Reference to original invoice eTIMS number
            $table->enum('etims_status', ['pending', 'submitted', 'approved', 'rejected'])->default('pending');

            // Application tracking
            $table->foreignId('applied_to_invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->decimal('applied_amount', 15, 2)->default(0); // Amount applied to invoice
            $table->decimal('remaining_credit', 15, 2)->default(0); // Remaining credit available

            // Notes
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();

            // Indexes
            $table->index('company_id');
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'credit_note_reference']);
            $table->index('invoice_id');
            $table->index('client_id');
            $table->index('user_id');
            $table->index('issue_date');
            $table->index('full_number');
            $table->index('etims_control_number');
            $table->index('etims_status');

            // Unique constraint for credit note reference per company
            $table->unique(['company_id', 'credit_note_reference'], 'credit_notes_company_reference_unique');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};
