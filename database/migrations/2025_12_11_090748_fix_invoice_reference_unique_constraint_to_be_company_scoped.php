<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fix invoice_reference unique constraint to be company-scoped.
     * This allows different companies to have the same invoice reference (e.g., "INV-001"),
     * but prevents duplicate invoice references within the same company.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the existing global unique constraint on invoice_reference
            // This constraint prevents different companies from having the same invoice number
            $table->dropUnique(['invoice_reference']);
        });

        // Add composite unique constraint on (company_id, invoice_reference)
        // This allows each company to have their own invoice numbering sequence
        DB::statement('
            ALTER TABLE invoices 
            ADD UNIQUE KEY invoices_company_id_invoice_reference_unique (company_id, invoice_reference)
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique(['company_id', 'invoice_reference']);
        });

        // Restore the original global unique constraint
        Schema::table('invoices', function (Blueprint $table) {
            $table->unique('invoice_reference');
        });
    }
};
