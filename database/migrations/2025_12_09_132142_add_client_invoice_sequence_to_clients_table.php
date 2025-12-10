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
        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedInteger('invoice_sequence_start')->default(1)->after('kra_pin');
            $table->unsignedInteger('next_invoice_sequence')->default(1)->after('invoice_sequence_start');
        });

        // Initialize next_invoice_sequence for existing clients
        \DB::statement('UPDATE clients SET next_invoice_sequence = invoice_sequence_start WHERE next_invoice_sequence IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['invoice_sequence_start', 'next_invoice_sequence']);
        });
    }
};
