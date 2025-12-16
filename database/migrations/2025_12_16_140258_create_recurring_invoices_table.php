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
        Schema::create('recurring_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Template name (e.g., "Monthly Retainer - Client ABC")
            $table->text('description')->nullable();
            
            // Frequency settings
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly'])->default('monthly');
            $table->unsignedInteger('interval')->default(1); // Every N periods (e.g., every 2 months)
            
            // Date settings
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Optional end date
            $table->date('next_run_date'); // When to generate next invoice
            $table->date('last_generated_at')->nullable();
            
            // Status
            $table->enum('status', ['active', 'paused', 'cancelled', 'completed'])->default('active');
            
            // Invoice template data (JSON)
            $table->json('invoice_data'); // Stores line items, notes, terms, etc.
            
            // Auto-send settings
            $table->boolean('auto_send')->default(false); // Automatically send when generated
            $table->boolean('send_reminders')->default(true); // Send payment reminders
            
            // Tracking
            $table->unsignedInteger('total_generated')->default(0);
            $table->unsignedInteger('max_occurrences')->nullable(); // Limit number of invoices
            
            $table->timestamps();
            
            $table->index(['company_id', 'status']);
            $table->index('next_run_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_invoices');
    }
};
