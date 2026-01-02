<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds polymorphic fields to payments table to support both invoice and subscription payments.
     * This allows payments to be associated with any payable model (Invoice, Subscription, etc.)
     *
     * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 2.2
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add polymorphic relationship fields
            $table->string('payable_type')->nullable()->after('invoice_id');
            $table->unsignedBigInteger('payable_id')->nullable()->after('payable_type');

            // Add idempotency key for duplicate callback prevention
            $table->string('idempotency_key')->nullable()->unique()->after('gateway_payment_intent_id');

            // Add raw gateway payload storage (for audit trail)
            $table->json('raw_gateway_payload')->nullable()->after('gateway_metadata');

            // Add composite index for polymorphic relationship
            $table->index(['payable_type', 'payable_id']);

            // Make invoice_id nullable (will use polymorphic relationship going forward)
            // Note: We keep invoice_id for backward compatibility, but new payments should use payable_type/payable_id
            $table->unsignedBigInteger('invoice_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['payable_type', 'payable_id']);
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn(['payable_type', 'payable_id', 'idempotency_key', 'raw_gateway_payload']);

            // Restore invoice_id to not nullable (if desired - may cause issues if nulls exist)
            // $table->unsignedBigInteger('invoice_id')->nullable(false)->change();
        });
    }
};
