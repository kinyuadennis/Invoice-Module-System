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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('etims_control_number')->nullable()->after('grand_total');
            $table->string('etims_qr_code')->nullable()->after('etims_control_number'); // Store QR code data
            $table->timestamp('etims_submitted_at')->nullable()->after('etims_qr_code');
            $table->json('etims_metadata')->nullable()->after('etims_submitted_at'); // Store eTIMS response data
            
            $table->index('etims_control_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['etims_control_number']);
            $table->dropColumn([
                'etims_control_number',
                'etims_qr_code',
                'etims_submitted_at',
                'etims_metadata',
            ]);
        });
    }
};
