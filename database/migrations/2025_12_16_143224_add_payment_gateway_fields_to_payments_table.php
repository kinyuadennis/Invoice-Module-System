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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway')->nullable()->after('payment_method'); // 'stripe', 'mpesa'
            $table->string('gateway_transaction_id')->nullable()->after('gateway');
            $table->string('gateway_payment_intent_id')->nullable()->after('gateway_transaction_id'); // For Stripe
            $table->json('gateway_metadata')->nullable()->after('gateway_payment_intent_id'); // Store additional gateway data
            $table->enum('gateway_status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->nullable()->after('gateway_metadata');

            $table->index('gateway_transaction_id');
            $table->index('gateway_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            //
        });
    }
};
