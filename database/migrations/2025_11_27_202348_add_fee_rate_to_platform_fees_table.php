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
        Schema::table('platform_fees', function (Blueprint $table) {
            $table->decimal('fee_rate', 5, 2)->default(0.8)->after('fee_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('platform_fees', function (Blueprint $table) {
            $table->dropColumn('fee_rate');
        });
    }
};
