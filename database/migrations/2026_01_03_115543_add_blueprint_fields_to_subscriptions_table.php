<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds missing fields per blueprint requirements:
     * - current_period_start: Start of current billing period
     * - current_period_end: End of current billing period
     * - gateway_subscription_id: Generic gateway subscription ID (replaces stripe_id for abstraction)
     *
     * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 2.1
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Add current_period_start and current_period_end for billing period tracking
            $table->timestamp('current_period_start')->nullable()->after('starts_at');
            $table->timestamp('current_period_end')->nullable()->after('current_period_start');

            // Add gateway_subscription_id (generic, replaces stripe_id for abstraction)
            // Keep stripe_id for backward compatibility with Cashier
            $table->string('gateway_subscription_id')->nullable()->after('gateway')->index();

            // Index for period queries
            $table->index(['current_period_start', 'current_period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['current_period_start', 'current_period_end']);
            $table->dropIndex(['gateway_subscription_id']);
            $table->dropColumn([
                'current_period_start',
                'current_period_end',
                'gateway_subscription_id',
            ]);
        });
    }
};
