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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('KES');
            $table->string('billing_period'); // 'monthly', 'yearly'
            $table->integer('max_companies')->nullable(); // null = unlimited
            $table->integer('max_users_per_company')->nullable();
            $table->integer('max_invoices_per_month')->nullable();
            $table->integer('max_clients')->nullable();
            $table->json('features')->nullable(); // Array of feature names
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
