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
            // Add nullable first to handle existing data
            $table->foreignId('company_id')->nullable()->after('id');
            $table->decimal('vat_amount', 10, 2)->default(0)->after('tax');
            $table->decimal('platform_fee', 10, 2)->default(0)->after('vat_amount');
            $table->decimal('grand_total', 10, 2)->default(0)->after('platform_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'vat_amount', 'platform_fee', 'grand_total']);
        });
    }
};
