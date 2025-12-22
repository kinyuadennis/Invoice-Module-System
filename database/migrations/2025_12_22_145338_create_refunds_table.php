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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('refund_reference')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending, processed, failed, cancelled
            $table->string('refund_method')->nullable(); // original_payment_method, cash, bank_transfer, etc.
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->date('refund_date');
            $table->dateTime('processed_at')->nullable();
            $table->json('metadata')->nullable(); // Store gateway refund IDs, transaction references, etc.
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['invoice_id', 'status']);
            $table->index('refund_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
