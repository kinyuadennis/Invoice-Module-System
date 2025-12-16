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
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('reminder_days_before_due')->default(3)->after('use_custom_email_templates');
            $table->boolean('reminder_enable_email')->default(true)->after('reminder_days_before_due');
            $table->boolean('reminder_enable_sms')->default(false)->after('reminder_enable_email');
            $table->integer('reminder_frequency_days')->default(7)->after('reminder_enable_sms');
            $table->integer('overdue_reminder_frequency_days')->default(3)->after('reminder_frequency_days');
            $table->time('reminder_send_time')->default('09:00')->after('overdue_reminder_frequency_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'reminder_days_before_due',
                'reminder_enable_email',
                'reminder_enable_sms',
                'reminder_frequency_days',
                'overdue_reminder_frequency_days',
                'reminder_send_time',
            ]);
        });
    }
};
