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
        Schema::create('billing_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('subscription_plan_id')->constrained()->onDelete('restrict');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('KES');
            $table->string('status'); // 'pending', 'paid', 'failed', 'refunded'
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Additional payment data
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index('paid_at');
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_history');
    }
};
