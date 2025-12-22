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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Admin", "Manager", "Staff"
            $table->string('slug')->unique(); // e.g., "admin", "manager", "staff"
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false); // System roles can't be deleted
            $table->boolean('is_active')->default(true);

            $table->index('company_id');
            $table->index(['company_id', 'is_active']);
            $table->unique(['company_id', 'slug']); // Unique role per company

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
