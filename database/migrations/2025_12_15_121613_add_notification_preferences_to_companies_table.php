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
            // Notification preferences
            $table->boolean('email_notifications_enabled')->default(true)->after('platform_fee_enabled');
            $table->boolean('whatsapp_notifications_enabled')->default(true)->after('email_notifications_enabled');
            $table->json('notification_preferences')->nullable()->after('whatsapp_notifications_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'email_notifications_enabled',
                'whatsapp_notifications_enabled',
                'notification_preferences',
            ]);
        });
    }
};
