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
            $table->string('invoice_reference')->nullable()->unique()->after('id');
            $table->string('payment_method')->nullable()->after('status');
            $table->text('payment_details')->nullable()->after('payment_method');
            $table->text('notes')->nullable()->after('payment_details');
            $table->date('issue_date')->nullable()->after('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['invoice_reference', 'payment_method', 'payment_details', 'notes', 'issue_date']);
        });
    }
};
