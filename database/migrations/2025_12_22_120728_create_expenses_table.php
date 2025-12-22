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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->onDelete('set null');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('recurring_expense_id')->nullable()->constrained('expenses')->onDelete('set null');

            $table->string('expense_number')->nullable(); // For tracking
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->string('description');
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable(); // File path for receipt
            $table->string('payment_method')->nullable(); // cash, mpesa, bank_transfer, card
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->boolean('tax_deductible')->default(true);
            $table->decimal('tax_amount', 15, 2)->default(0); // Calculated tax if applicable
            $table->string('reference_number')->nullable(); // Payment reference
            $table->string('vendor_name')->nullable(); // Who was paid

            $table->index('company_id');
            $table->index('user_id');
            $table->index('category_id');
            $table->index('client_id');
            $table->index('invoice_id');
            $table->index('expense_date');
            $table->index('status');
            $table->index(['company_id', 'expense_date']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
