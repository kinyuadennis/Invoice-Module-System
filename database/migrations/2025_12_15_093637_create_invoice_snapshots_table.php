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
            $table->id();
            $table->foreignId('invoice_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('snapshot_taken_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('snapshot_data'); // Complete financial truth payload
            $table->timestamp('snapshot_taken_at');
            $table->boolean('legacy_snapshot')->default(false);
            $table->timestamps();

            // Index for querying snapshots
            $table->index('invoice_id');
            $table->index('snapshot_taken_at');
            $table->index('legacy_snapshot');
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
