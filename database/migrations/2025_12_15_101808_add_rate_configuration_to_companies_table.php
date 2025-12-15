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
        Schema::table('companies', function (Blueprint $table) {
            // VAT configuration
            $table->decimal('default_vat_rate', 5, 2)->default(16.00)->after('currency');
            $table->boolean('vat_enabled')->default(true)->after('default_vat_rate');

            // Platform fee configuration
            $table->decimal('platform_fee_rate', 5, 4)->default(0.03)->after('vat_enabled');
            $table->boolean('platform_fee_enabled')->default(true)->after('platform_fee_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'default_vat_rate',
                'vat_enabled',
                'platform_fee_rate',
                'platform_fee_enabled',
            ]);
        });
    }
};
