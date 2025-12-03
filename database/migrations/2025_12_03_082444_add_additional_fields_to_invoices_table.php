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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('po_number')->nullable()->after('invoice_reference');
            $table->text('terms_and_conditions')->nullable()->after('notes');
            $table->boolean('vat_registered')->default(false)->after('status');
            $table->decimal('discount', 10, 2)->nullable()->default(0)->after('subtotal');
            $table->string('discount_type', 20)->nullable()->default('fixed')->after('discount'); // 'fixed' or 'percentage'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['po_number', 'terms_and_conditions', 'vat_registered', 'discount', 'discount_type']);
        });
    }
};
