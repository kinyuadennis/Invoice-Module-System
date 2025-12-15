<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'finalized' to status enum
        // Using raw SQL because Laravel Schema builder doesn't support enum modifications
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'finalized', 'sent', 'paid', 'overdue', 'cancelled') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'finalized' from enum (revert to original)
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') NOT NULL DEFAULT 'draft'");
    }
};
