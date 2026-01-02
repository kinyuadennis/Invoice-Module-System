<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Drops the platform_fees table entirely as part of migration from platform
     * fee model to subscription-based payment model.
     * 
     * Note: This migration should run after removing platform_fee columns from
     * invoices, estimates, and credit_notes tables to maintain referential integrity.
     */
    public function up(): void
    {
        Schema::dropIfExists('platform_fees');
    }

    /**
     * Reverse the migrations.
     * 
     * Recreates the platform_fees table structure. This is provided for rollback
     * purposes only. Historical data will not be restored.
     */
    public function down(): void
    {
        Schema::create('platform_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->decimal('fee_amount', 10, 2);
            $table->decimal('fee_rate', 5, 2)->nullable();
            $table->enum('fee_status', ['pending', 'paid', 'waived'])->default('pending');
            $table->timestamps();
            
            $table->index('company_id');
        });
    }
};

