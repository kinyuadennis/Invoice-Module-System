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
        Schema::create('estimates', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('invoice_templates')->onDelete('set null');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('converted_to_invoice_id')->nullable()->constrained('invoices')->onDelete('set null');

            // Estimate numbering (similar to invoices)
            $table->string('estimate_reference')->nullable();
            $table->string('prefix_used')->nullable();
            $table->unsignedInteger('serial_number')->nullable();
            $table->unsignedInteger('client_sequence')->nullable();
            $table->string('estimate_number')->nullable();
            $table->string('full_number')->nullable();

            // Status: draft, sent, accepted, rejected, expired, converted
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired', 'converted'])->default('draft');

            // Dates
            $table->date('issue_date');
            $table->date('expiry_date')->nullable(); // When estimate expires
            $table->date('accepted_at')->nullable();
            $table->date('rejected_at')->nullable();

            // Details
            $table->string('po_number')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->boolean('vat_registered')->default(false);

            // Financials
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->string('discount_type')->default('fixed'); // fixed or percentage
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('platform_fee', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            // Indexes
            $table->index('company_id');
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'estimate_reference']);
            $table->index('client_id');
            $table->index('user_id');
            $table->index('expiry_date');
            $table->index('full_number');

            // Unique constraint for estimate reference per company
            $table->unique(['company_id', 'estimate_reference'], 'estimates_company_estimate_reference_unique');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimates');
    }
};
