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
        Schema::table('invoice_snapshots', function (Blueprint $table) {
            // Add explicit fields for notes and terms to ensure they're always captured
            // These will be stored in snapshot_data as well, but having explicit fields
            // makes queries and PDF generation more reliable
            $table->text('notes')->nullable()->after('snapshot_data');
            $table->text('terms_and_conditions')->nullable()->after('notes');
            
            // Add currency field to snapshot for PDF consistency
            $table->string('currency', 3)->nullable()->after('terms_and_conditions');
            
            // Add buyer's KRA PIN for eTIMS compliance (2026 requirements)
            $table->string('buyer_kra_pin', 11)->nullable()->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_snapshots', function (Blueprint $table) {
            $table->dropColumn(['notes', 'terms_and_conditions', 'currency', 'buyer_kra_pin']);
        });
    }
};
