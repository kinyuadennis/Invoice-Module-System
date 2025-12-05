<?php

namespace Database\Seeders;

use App\Models\InvoiceTemplate;
use Illuminate\Database\Seeder;

class InvoiceTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Modern Clean',
                'slug' => 'modern-clean',
                'prefix' => 'INV',
                'description' => 'Clean, modern design with ample white space and professional typography.',
                'layout_class' => 'template-modern-clean',
                'css_file' => 'modern-clean.css',
                'view_path' => 'invoices.templates.modern-clean',
                'preview_image' => 'modern-clean-preview.png',
                'is_active' => true,
                'is_default' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Classic Professional',
                'slug' => 'classic-professional',
                'prefix' => 'FACT',
                'description' => 'Traditional invoice layout with formal styling and structured sections.',
                'layout_class' => 'template-classic-professional',
                'css_file' => 'classic-professional.css',
                'view_path' => 'invoices.templates.classic-professional',
                'preview_image' => 'classic-professional-preview.png',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 2,
            ],
            [
                'name' => 'Minimalist',
                'slug' => 'minimalist',
                'prefix' => 'BILL',
                'description' => 'Ultra-minimal design focusing on essential information only.',
                'layout_class' => 'template-minimalist',
                'css_file' => 'minimalist.css',
                'view_path' => 'invoices.templates.minimalist-neutral',
                'preview_image' => 'minimalist-preview.png',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 3,
            ],
            [
                'name' => 'Bold Modern',
                'slug' => 'bold-modern',
                'prefix' => 'INV',
                'description' => 'Bold colors and modern design elements for standout invoices.',
                'layout_class' => 'template-bold-modern',
                'css_file' => 'bold-modern.css',
                'view_path' => 'invoices.templates.accent-header',
                'preview_image' => 'bold-modern-preview.png',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 4,
            ],
        ];

        foreach ($templates as $template) {
            InvoiceTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
