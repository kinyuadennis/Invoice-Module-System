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
        // Add approval fields to invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('requires_approval')->default(false)->after('status');
            $table->string('approval_status')->nullable()->after('requires_approval'); // pending, approved, rejected
        });

        // Add approval fields to estimates
        Schema::table('estimates', function (Blueprint $table) {
            $table->boolean('requires_approval')->default(false)->after('status');
            $table->string('approval_status')->nullable()->after('requires_approval'); // pending, approved, rejected
        });

        // Add approval fields to expenses
        Schema::table('expenses', function (Blueprint $table) {
            $table->boolean('requires_approval')->default(false)->after('status');
            $table->string('approval_status')->nullable()->after('requires_approval'); // pending, approved, rejected
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['requires_approval', 'approval_status']);
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn(['requires_approval', 'approval_status']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['requires_approval', 'approval_status']);
        });
    }
};
