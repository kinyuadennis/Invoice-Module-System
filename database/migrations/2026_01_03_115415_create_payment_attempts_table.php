<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates payment_attempts table per blueprint requirements.
     * Tracks all payment attempts (initiated, pending, succeeded, failed, timed_out)
     * without polluting the immutable payments table.
     *
     * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 2.1
     */
    public function up(): void
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('KES');
            $table->string('gateway', 10); // 'mpesa' or 'stripe'
            $table->string('gateway_transaction_id')->nullable()->index();
            $table->integer('attempt_number')->default(1);
            $table->string('status', 20); // initiated, pending, succeeded, failed, timed_out
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->json('gateway_metadata')->nullable();
            $table->json('raw_gateway_payload')->nullable();
            $table->string('idempotency_key')->unique()->nullable();
            $table->timestamp('initiated_at');
            $table->timestamp('succeeded_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            // Indexes for queries
            $table->index(['subscription_id', 'status']);
            $table->index(['gateway_transaction_id']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};
