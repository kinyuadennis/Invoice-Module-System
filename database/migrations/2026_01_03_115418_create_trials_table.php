<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates trials table per blueprint requirements.
     * Single source of truth for trial status (separate to avoid polluting subscriptions).
     *
     * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 2.1
     */
    public function up(): void
    {
        Schema::create('trials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->unique()->constrained()->onDelete('cascade');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('extended')->default(false);
            $table->string('status', 20)->default('active'); // active, expired
            $table->timestamps();

            // Indexes for queries
            $table->index(['status', 'ends_at']);
            $table->index('ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trials');
    }
};
