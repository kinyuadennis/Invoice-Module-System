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
            $table->text('email_template_invoice_sent_subject')->nullable()->after('settings');
            $table->text('email_template_invoice_sent_body')->nullable()->after('email_template_invoice_sent_subject');
            $table->text('email_template_payment_reminder_subject')->nullable()->after('email_template_invoice_sent_body');
            $table->text('email_template_payment_reminder_body')->nullable()->after('email_template_payment_reminder_subject');
            $table->boolean('use_custom_email_templates')->default(false)->after('email_template_payment_reminder_body');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'email_template_invoice_sent_subject',
                'email_template_invoice_sent_body',
                'email_template_payment_reminder_subject',
                'email_template_payment_reminder_body',
                'use_custom_email_templates',
            ]);
        });
    }
};
