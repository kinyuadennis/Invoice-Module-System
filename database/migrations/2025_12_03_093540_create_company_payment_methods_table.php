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
        Schema::create('company_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['bank_transfer', 'mpesa', 'paypal', 'stripe', 'mobile_money', 'cash'])->default('bank_transfer');
            $table->string('name')->nullable(); // Custom name for the payment method
            $table->boolean('is_enabled')->default(true);
            $table->integer('sort_order')->default(0);
            
            // Bank Transfer fields
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('branch_code')->nullable();
            $table->text('bank_instructions')->nullable();
            
            // MPesa fields
            $table->string('mpesa_paybill')->nullable();
            $table->string('mpesa_account_number')->nullable();
            $table->text('mpesa_instructions')->nullable();
            
            // PayPal/Stripe fields
            $table->string('payment_link')->nullable();
            $table->string('merchant_id')->nullable();
            $table->text('online_instructions')->nullable();
            
            // Mobile Money fields
            $table->string('mobile_money_provider')->nullable(); // e.g., "Airtel Money", "Tigo Pesa"
            $table->string('mobile_money_number')->nullable();
            $table->text('mobile_money_instructions')->nullable();
            
            // Cash fields
            $table->text('cash_instructions')->nullable();
            
            // Expected clearing time (in days)
            $table->integer('clearing_days')->default(0); // 0 = instant, 1-3 = days
            
            $table->timestamps();
            
            $table->index(['company_id', 'is_enabled', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_payment_methods');
    }
};
