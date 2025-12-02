<?php

return [
    'templates' => [
        'modern_clean' => [
            'name' => 'Modern Clean',
            'description' => 'Clean, minimalist design with modern typography',
            'thumbnail' => 'templates/modern-clean-thumb.png',
            'view' => 'invoices.templates.modern-clean',
        ],
        'classic_professional' => [
            'name' => 'Classic Professional',
            'description' => 'Traditional business invoice with formal layout',
            'thumbnail' => 'templates/classic-professional-thumb.png',
            'view' => 'invoices.templates.classic-professional',
        ],
        'accent_header' => [
            'name' => 'Accent Header',
            'description' => 'Bold header with accent colors',
            'thumbnail' => 'templates/accent-header-thumb.png',
            'view' => 'invoices.templates.accent-header',
        ],
        'minimalist_neutral' => [
            'name' => 'Minimalist Neutral',
            'description' => 'Ultra-minimal design with neutral colors',
            'thumbnail' => 'templates/minimalist-neutral-thumb.png',
            'view' => 'invoices.templates.minimalist-neutral',
        ],
    ],

    'format_patterns' => [
        '{PREFIX}-{NUMBER}' => 'INV-0001',
        '{PREFIX}-{YEAR}-{NUMBER}' => 'INV-2025-0001',
        '{YEAR}/{NUMBER}' => '2025/0001',
        '{PREFIX}/{NUMBER}/{SUFFIX}' => 'INV/0001/KE',
        '{NUMBER}' => '0001',
    ],
];
