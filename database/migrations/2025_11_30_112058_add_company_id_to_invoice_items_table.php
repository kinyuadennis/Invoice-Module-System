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
        Schema::table('invoice_items', function (Blueprint $table) {
            // Add nullable first to handle existing data
            $table->foreignId('company_id')->nullable()->after('id');
            $table->boolean('vat_included')->default(false)->after('unit_price');
            $table->decimal('vat_rate', 5, 2)->default(16.00)->after('vat_included');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'vat_included', 'vat_rate']);
        });
    }
};
