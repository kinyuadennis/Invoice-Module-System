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
            // Make client_id nullable to allow drafts without a client
            $table->foreignId('client_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Note: This might fail if there are invoices with null client_id
            // In production, you'd want to handle this more carefully
            $table->foreignId('client_id')->nullable(false)->change();
        });
    }
};
