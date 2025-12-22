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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained()->onDelete('set null'); // Link to items table
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');

            $table->string('sku')->nullable(); // Stock Keeping Unit
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('unit_of_measure')->default('pcs'); // pcs, kg, ltr, etc.

            // Pricing
            $table->decimal('cost_price', 15, 2)->default(0); // Purchase cost
            $table->decimal('selling_price', 15, 2)->default(0); // Selling price
            $table->decimal('unit_price', 15, 2)->default(0); // Alias for selling_price

            // Stock Management
            $table->decimal('current_stock', 10, 2)->default(0);
            $table->decimal('minimum_stock', 10, 2)->default(0); // Reorder point
            $table->decimal('maximum_stock', 10, 2)->nullable(); // Max stock level
            $table->boolean('track_stock')->default(true);
            $table->boolean('auto_deduct_on_invoice')->default(true);

            // Location/Warehouse
            $table->string('location')->nullable(); // Warehouse/location
            $table->string('barcode')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            $table->index('company_id');
            $table->index('item_id');
            $table->index('supplier_id');
            $table->index('sku');
            $table->index(['company_id', 'track_stock', 'is_active']);
            $table->index(['company_id', 'current_stock']); // For low stock queries

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
