<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Add index on company_id if it doesn't exist
            $indexExists = DB::selectOne("
                SELECT COUNT(*) as count
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                AND table_name = 'invoices'
                AND index_name = 'invoices_company_id_index'
            ");
            
            if ($indexExists && $indexExists->count == 0) {
                $table->index('company_id', 'invoices_company_id_index');
            }
            
            // Add composite index on (company_id, status) for filtered queries
            $compositeIndexExists = DB::selectOne("
                SELECT COUNT(*) as count
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                AND table_name = 'invoices'
                AND index_name = 'invoices_company_id_status_index'
            ");
            
            if ($compositeIndexExists && $compositeIndexExists->count == 0) {
                $table->index(['company_id', 'status'], 'invoices_company_id_status_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_company_id_index');
            $table->dropIndex('invoices_company_id_status_index');
        });
    }
};
