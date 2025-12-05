<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('invoice_template_id')
                ->nullable()
                ->after('invoice_template')
                ->constrained('invoice_templates')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        // Migrate existing invoice_template string values to foreign keys
        $this->migrateExistingTemplates();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['invoice_template_id']);
            $table->dropColumn('invoice_template_id');
        });
    }

    /**
     * Migrate existing invoice_template string values to foreign keys.
     */
    private function migrateExistingTemplates(): void
    {
        // Only run if invoice_templates table exists and has data
        if (!Schema::hasTable('invoice_templates')) {
            return;
        }

        // Get all unique invoice_template values from companies
        $templates = DB::table('companies')
            ->whereNotNull('invoice_template')
            ->where('invoice_template', '!=', '')
            ->distinct()
            ->pluck('invoice_template');

        foreach ($templates as $templateName) {
            // Find or create template by slug
            $slug = \Illuminate\Support\Str::slug($templateName);

            $template = DB::table('invoice_templates')
                ->where('slug', $slug)
                ->first();

            if (!$template) {
                // Create template if it doesn't exist
                $templateId = DB::table('invoice_templates')->insertGetId([
                    'name' => ucwords(str_replace(['-', '_'], ' ', $templateName)),
                    'slug' => $slug,
                    'prefix' => 'INV', // Default prefix
                    'view_path' => "invoices.templates.{$slug}",
                    'is_active' => true,
                    'is_default' => false,
                    'display_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $templateId = $template->id;
            }

            // Update companies with this template name to use the foreign key
            DB::table('companies')
                ->where('invoice_template', $templateName)
                ->update([
                    'invoice_template_id' => $templateId,
                ]);
        }
    }
};
