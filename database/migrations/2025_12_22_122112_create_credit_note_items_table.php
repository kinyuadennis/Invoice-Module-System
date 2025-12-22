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
        Schema::create('credit_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('credit_note_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_item_id')->nullable()->constrained('invoice_items')->onDelete('set null'); // Link to original invoice item
            $table->foreignId('item_id')->nullable()->constrained()->onDelete('set null');

            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->boolean('vat_included')->default(false);
            $table->decimal('vat_rate', 5, 2)->default(16.00);
            $table->decimal('total_price', 15, 2);

            // Reason for crediting this item
            $table->enum('credit_reason', ['returned', 'damaged', 'wrong_item', 'adjustment', 'other'])->default('other');
            $table->text('credit_reason_details')->nullable();

            $table->index('company_id');
            $table->index('credit_note_id');
            $table->index('invoice_item_id');
            $table->index('item_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_note_items');
    }
};
