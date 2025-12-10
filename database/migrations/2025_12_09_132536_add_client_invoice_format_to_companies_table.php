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
            $table->string('client_invoice_format', 100)->nullable()->after('invoice_format');
            $table->boolean('use_client_specific_numbering')->default(false)->after('client_invoice_format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['client_invoice_format', 'use_client_specific_numbering']);
        });
    }
};
