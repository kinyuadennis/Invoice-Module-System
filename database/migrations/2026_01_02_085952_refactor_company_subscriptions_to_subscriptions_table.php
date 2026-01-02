<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Refactors company_subscriptions table to support blueprint requirements:
     * - Renames table from company_subscriptions to subscriptions
     * - Adds user_id for user-based subscriptions
     * - Adds plan_code for plan identification
     * - Adds gateway field for payment gateway (mpesa/stripe)
     * - Adds next_billing_at for renewal scheduling
     * - Keeps existing fields for backward compatibility
     *
     * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 2.1
     */
    public function up(): void
    {
        // First, add new columns to the existing table
        Schema::table('company_subscriptions', function (Blueprint $table) {
            // Add user_id (nullable for backward compatibility, required for new subscriptions)
            $table->foreignId('user_id')->nullable()->after('company_id')->constrained()->onDelete('cascade');

            // Add plan_code (string identifier for plans, e.g., 'pro', 'basic')
            $table->string('plan_code')->nullable()->after('subscription_plan_id');

            // Add gateway field (mpesa or stripe)
            $table->string('gateway', 10)->nullable()->after('status');

            // Add next_billing_at for renewal scheduling
            $table->datetime('next_billing_at')->nullable()->after('ends_at');

            // Add index on user_id for lookups
            $table->index('user_id');

            // Add index on gateway for gateway-specific queries
            $table->index('gateway');

            // Add index on next_billing_at for renewal scheduling
            $table->index('next_billing_at');
        });

        // Rename the table from company_subscriptions to subscriptions
        // Note: Foreign key constraints referencing this table will be automatically updated by the database
        Schema::rename('company_subscriptions', 'subscriptions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename the table back to company_subscriptions
        Schema::rename('subscriptions', 'company_subscriptions');

        // Remove the added columns
        Schema::table('company_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['next_billing_at']);
            $table->dropIndex(['gateway']);
            $table->dropIndex(['user_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'plan_code', 'gateway', 'next_billing_at']);
        });
    }
};
