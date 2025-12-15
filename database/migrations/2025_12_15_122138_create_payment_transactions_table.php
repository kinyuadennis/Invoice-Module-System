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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->string('gateway')->index(); // e.g., 'mpesa', 'stripe'
            $table->string('transaction_reference')->unique()->index();
            $table->string('transaction_id')->nullable()->index();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->decimal('amount', 10, 2);
            $table->json('gateway_payload')->nullable(); // Store full webhook payload
            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes for efficient lookups
            $table->index(['company_id', 'status']);
            $table->index(['invoice_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
