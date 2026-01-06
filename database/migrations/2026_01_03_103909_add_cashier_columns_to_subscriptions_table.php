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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Cashier columns for Stripe integration
            $table->string('stripe_id')->nullable()->unique()->after('gateway');
            $table->string('stripe_status')->nullable()->after('stripe_id');
            $table->string('stripe_price')->nullable()->after('stripe_status');
            $table->string('type')->default('default')->after('stripe_price');
            $table->integer('quantity')->nullable()->after('type');

            // Index for Cashier queries
            $table->index(['user_id', 'stripe_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'stripe_status']);
            $table->dropColumn([
                'stripe_id',
                'stripe_status',
                'stripe_price',
                'type',
                'quantity',
            ]);
        });
    }
};
