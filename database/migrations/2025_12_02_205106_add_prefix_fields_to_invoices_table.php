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
            $table->string('prefix_used', 50)->nullable()->after('invoice_reference');
            $table->unsignedInteger('serial_number')->nullable()->after('prefix_used');
            $table->string('full_number', 100)->nullable()->after('serial_number');

            $table->index(['company_id', 'prefix_used', 'serial_number']);
            $table->index('full_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'prefix_used', 'serial_number']);
            $table->dropIndex(['full_number']);
            $table->dropColumn(['prefix_used', 'serial_number', 'full_number']);
        });
    }
};
