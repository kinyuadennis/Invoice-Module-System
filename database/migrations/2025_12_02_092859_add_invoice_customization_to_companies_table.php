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
            // Invoice number format customization
            $table->string('invoice_suffix', 20)->nullable()->after('invoice_prefix');
            $table->integer('invoice_padding')->default(4)->after('invoice_suffix');
            $table->string('invoice_format', 50)->default('{PREFIX}-{NUMBER}')->after('invoice_padding');

            // Invoice template selection
            $table->string('invoice_template', 50)->default('modern_clean')->after('invoice_format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['invoice_suffix', 'invoice_padding', 'invoice_format', 'invoice_template']);
        });
    }
};
