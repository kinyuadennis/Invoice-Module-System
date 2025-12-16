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
        Schema::table('invoice_reminder_logs', function (Blueprint $table) {
            $table->string('channel')->default('email')->after('reminder_type'); // 'email' or 'sms'
            $table->string('recipient_phone')->nullable()->after('recipient_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_reminder_logs', function (Blueprint $table) {
            $table->dropColumn(['channel', 'recipient_phone']);
        });
    }
};
