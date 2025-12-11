<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add indexes to company_id columns to improve query performance.
     * This prevents full table scans when filtering by company_id.
     */
    public function up(): void
    {
        // Add index to invoices table if it doesn't exist
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'company_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                // Check if index doesn't already exist by querying information_schema
                $indexExists = DB::selectOne("
                    SELECT COUNT(*) as count
                    FROM information_schema.statistics
                    WHERE table_schema = DATABASE()
                    AND table_name = 'invoices'
                    AND index_name = 'invoices_company_id_index'
                ");
                
                if (! $indexExists || $indexExists->count == 0) {
                    $table->index('company_id');
                }
            });
        }

        // Add index to clients table if it doesn't exist
        if (Schema::hasTable('clients') && Schema::hasColumn('clients', 'company_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $indexExists = DB::selectOne("
                    SELECT COUNT(*) as count
                    FROM information_schema.statistics
                    WHERE table_schema = DATABASE()
                    AND table_name = 'clients'
                    AND index_name = 'clients_company_id_index'
                ");
                
                if (! $indexExists || $indexExists->count == 0) {
                    $table->index('company_id');
                }
            });
        }

        // Add index to payments table if it doesn't exist
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'company_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $indexExists = DB::selectOne("
                    SELECT COUNT(*) as count
                    FROM information_schema.statistics
                    WHERE table_schema = DATABASE()
                    AND table_name = 'payments'
                    AND index_name = 'payments_company_id_index'
                ");
                
                if (! $indexExists || $indexExists->count == 0) {
                    $table->index('company_id');
                }
            });
        }

        // Add index to platform_fees table if it doesn't exist
        if (Schema::hasTable('platform_fees') && Schema::hasColumn('platform_fees', 'company_id')) {
            Schema::table('platform_fees', function (Blueprint $table) {
                $indexExists = DB::selectOne("
                    SELECT COUNT(*) as count
                    FROM information_schema.statistics
                    WHERE table_schema = DATABASE()
                    AND table_name = 'platform_fees'
                    AND index_name = 'platform_fees_company_id_index'
                ");
                
                if (! $indexExists || $indexExists->count == 0) {
                    $table->index('company_id');
                }
            });
        }

        // Add index to invoice_items table if it doesn't exist
        if (Schema::hasTable('invoice_items') && Schema::hasColumn('invoice_items', 'company_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $indexExists = DB::selectOne("
                    SELECT COUNT(*) as count
                    FROM information_schema.statistics
                    WHERE table_schema = DATABASE()
                    AND table_name = 'invoice_items'
                    AND index_name = 'invoice_items_company_id_index'
                ");
                
                if (! $indexExists || $indexExists->count == 0) {
                    $table->index('company_id');
                }
            });
        }

        // services table already has foreign key constraint which creates an index
        // items table already has index on company_id
        // company_payment_methods already has composite index including company_id
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes if they exist
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'company_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropIndex(['company_id']);
            });
        }

        if (Schema::hasTable('clients') && Schema::hasColumn('clients', 'company_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropIndex(['company_id']);
            });
        }

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'company_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropIndex(['company_id']);
            });
        }

        if (Schema::hasTable('platform_fees') && Schema::hasColumn('platform_fees', 'company_id')) {
            Schema::table('platform_fees', function (Blueprint $table) {
                $table->dropIndex(['company_id']);
            });
        }

        if (Schema::hasTable('invoice_items') && Schema::hasColumn('invoice_items', 'company_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->dropIndex(['company_id']);
            });
        }
    }
};
