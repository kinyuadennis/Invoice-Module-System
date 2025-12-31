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
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('fraud_status', ['pending', 'approved', 'flagged', 'rejected'])->default('pending')->after('status');
            $table->decimal('fraud_score', 5, 2)->nullable()->after('fraud_status');
            $table->json('fraud_checks')->nullable()->after('fraud_score');
            $table->text('fraud_reason')->nullable()->after('fraud_checks');
            $table->string('ip_address', 45)->nullable()->after('fraud_reason');
            $table->string('user_agent')->nullable()->after('ip_address');
            $table->string('device_fingerprint')->nullable()->after('user_agent');
            $table->timestamp('fraud_reviewed_at')->nullable()->after('device_fingerprint');
            $table->foreignId('fraud_reviewed_by')->nullable()->constrained('users')->onDelete('set null')->after('fraud_reviewed_at');
            $table->integer('retry_count')->default(0)->after('fraud_reviewed_by');
            $table->timestamp('last_retry_at')->nullable()->after('retry_count');

            $table->index('fraud_status');
            $table->index(['company_id', 'fraud_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['fraud_reviewed_by']);
            $table->dropIndex(['fraud_status']);
            $table->dropIndex(['company_id', 'fraud_status']);
            $table->dropColumn([
                'fraud_status',
                'fraud_score',
                'fraud_checks',
                'fraud_reason',
                'ip_address',
                'user_agent',
                'device_fingerprint',
                'fraud_reviewed_at',
                'fraud_reviewed_by',
                'retry_count',
                'last_retry_at',
            ]);
        });
    }
};
