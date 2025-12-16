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
        Schema::create('invoice_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('reminder_type'); // 'due_soon', 'overdue', 'invoice_sent'
            $table->timestamp('sent_at');
            $table->string('recipient_email');
            $table->boolean('sent_successfully')->default(true);
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Index for querying recent reminders
            $table->index(['invoice_id', 'reminder_type', 'sent_at']);
            $table->index(['company_id', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_reminder_logs');
    }
};
