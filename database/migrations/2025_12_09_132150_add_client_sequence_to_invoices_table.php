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
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedInteger('client_sequence')->nullable()->after('serial_number');
            $table->string('invoice_number')->nullable()->after('client_sequence');

            // Add index for client-specific queries
            $table->index(['client_id', 'client_sequence'], 'invoices_client_id_client_sequence_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_client_id_client_sequence_index');
            $table->dropColumn(['client_sequence', 'invoice_number']);
        });
    }
};
