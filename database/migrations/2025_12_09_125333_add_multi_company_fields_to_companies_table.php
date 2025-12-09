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
            $table->string('currency', 3)->default('KES')->after('kra_pin');
            $table->string('timezone', 50)->default('Africa/Nairobi')->after('currency');
            $table->unsignedBigInteger('next_invoice_sequence')->default(1)->after('invoice_template_id');

            // Rename invoice_template_id to default_invoice_template_id for clarity
            // But keep both for backward compatibility during transition
            $table->unsignedBigInteger('default_invoice_template_id')->nullable()->after('invoice_template_id');
            $table->foreign('default_invoice_template_id')
                ->references('id')
                ->on('invoice_templates')
                ->onDelete('set null');
        });

        // Copy invoice_template_id to default_invoice_template_id for existing records
        \DB::statement('UPDATE companies SET default_invoice_template_id = invoice_template_id WHERE invoice_template_id IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['default_invoice_template_id']);
            $table->dropColumn(['currency', 'timezone', 'next_invoice_sequence', 'default_invoice_template_id']);
        });
    }
};
