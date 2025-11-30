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
        // Delete orphaned clients that don't belong to any user/company
        // This is safe for a fresh migration to multi-tenant
        DB::table('clients')->whereNull('user_id')->delete();

        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id');
            }
            if (! Schema::hasColumn('clients', 'kra_pin')) {
                $table->string('kra_pin')->nullable()->after('address');
            }
        });

        // For existing clients, we'll keep company_id nullable
        // The application will enforce company_id requirement for new records
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'company_id')) {
                if (Schema::hasTable('clients')) {
                    $foreignKeys = DB::select(
                        "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                         WHERE TABLE_SCHEMA = DATABASE() 
                         AND TABLE_NAME = 'clients' 
                         AND COLUMN_NAME = 'company_id' 
                         AND REFERENCED_TABLE_NAME IS NOT NULL"
                    );
                    foreach ($foreignKeys as $key) {
                        $table->dropForeign([$key->CONSTRAINT_NAME]);
                    }
                }
                $table->dropColumn('company_id');
            }
            if (Schema::hasColumn('clients', 'kra_pin')) {
                $table->dropColumn('kra_pin');
            }
        });
    }
};
