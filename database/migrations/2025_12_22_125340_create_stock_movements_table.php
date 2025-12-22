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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Movement type: purchase, sale, adjustment, return, transfer
            $table->enum('type', ['purchase', 'sale', 'adjustment', 'return', 'transfer', 'opening_stock'])->default('adjustment');

            // Quantity change (positive for increase, negative for decrease)
            $table->decimal('quantity', 10, 2);

            // Stock levels before and after
            $table->decimal('stock_before', 10, 2);
            $table->decimal('stock_after', 10, 2);

            // Reference to related documents
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null'); // For sales
            $table->foreignId('estimate_id')->nullable()->constrained()->onDelete('set null'); // For estimates
            $table->foreignId('credit_note_id')->nullable()->constrained()->onDelete('set null'); // For returns
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null'); // For purchases

            // Details
            $table->text('notes')->nullable();
            $table->string('reference_number')->nullable(); // PO number, invoice number, etc.
            $table->date('movement_date');
            $table->decimal('unit_cost', 15, 2)->nullable(); // Cost per unit at time of movement

            $table->index('company_id');
            $table->index('inventory_item_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('movement_date');
            $table->index('invoice_id');
            $table->index(['company_id', 'movement_date']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
