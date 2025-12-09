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
        Schema::table('users', function (Blueprint $table) {
            // Track the currently active/selected company for the user
            $table->unsignedBigInteger('active_company_id')->nullable()->after('company_id');
            $table->foreign('active_company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('set null');
        });

        // Set active_company_id to company_id for existing users
        \DB::statement('UPDATE users SET active_company_id = company_id WHERE company_id IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['active_company_id']);
            $table->dropColumn('active_company_id');
        });
    }
};
