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
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->morphs('approvable'); // approvable_type, approvable_id (Invoice, Estimate, Expense)
            $table->foreignId('requested_by_user_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, approved, rejected, cancelled
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('rejected_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Store additional approval data
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index('requested_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
