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
        Schema::create('invoice_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action_type', 50)->index(); // create, update, finalize, send, pay, PDF_generate, API_access, ETIMS_export
            $table->json('old_data')->nullable(); // Previous state (for updates)
            $table->json('new_data')->nullable(); // New state (for updates)
            $table->json('metadata')->nullable(); // Additional context (IP, user agent, etc.)
            $table->string('ip_address', 45)->nullable(); // IPv4 or IPv6
            $table->string('user_agent')->nullable();
            $table->string('source')->default('ui'); // ui, api, integration, job
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['invoice_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action_type', 'created_at']);
            $table->index('created_at'); // For retention policy cleanup
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_audit_logs');
    }
};
