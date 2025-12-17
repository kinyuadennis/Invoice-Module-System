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
        Schema::create('invoice_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('status')->index(); // draft, sent, paid, overdue
            $table->json('snapshot_data'); // Stores the full immutable state
            $table->string('triggered_by')->nullable(); // User who triggered the snapshot
            $table->timestamps();
            
            // Indexes for faster retrieval
            $table->index(['invoice_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_snapshots');
    }
};
