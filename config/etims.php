<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ETIMS Export Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for ETIMS (Electronic Tax Invoice Management System)
    | export functionality. This defines field mappings and requirements
    | for generating ETIMS-compliant export files.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Field Mappings
    |--------------------------------------------------------------------------
    |
    | Maps snapshot data paths to ETIMS field names.
    | Path format: dot notation (e.g., 'invoice.invoice_number')
    |
    */
    'field_mappings' => [
        'invoiceNumber' => 'invoice.invoice_number',
        'issueDate' => 'invoice.issue_date',
        'dueDate' => 'invoice.due_date',
        'currency' => 'invoice.currency',
        'poNumber' => 'invoice.po_number',
        'notes' => 'invoice.notes',
        'termsAndConditions' => 'invoice.terms_and_conditions',

        // Company (Seller) fields
        'seller.kraPin' => 'company.kra_pin',
        'seller.name' => 'company.name',
        'seller.email' => 'company.email',
        'seller.phone' => 'company.phone',
        'seller.address' => 'company.address',
        'seller.registrationNumber' => 'company.registration_number',

        // Client (Buyer) fields
        'buyer.kraPin' => 'client.kra_pin',
        'buyer.name' => 'client.name',
        'buyer.email' => 'client.email',
        'buyer.phone' => 'client.phone',
        'buyer.address' => 'client.address',

        // Totals
        'totals.subtotal' => 'totals.subtotal',
        'totals.discount' => 'totals.discount',
        'totals.vatAmount' => 'totals.vat_amount',
        'totals.platformFee' => 'totals.platform_fee',
        'totals.total' => 'totals.grand_total',
    ],

    /*
    |--------------------------------------------------------------------------
    | Required Fields
    |--------------------------------------------------------------------------
    |
    | Fields that must be present for ETIMS export to succeed.
    | Missing required fields will cause export to fail.
    |
    */
    'required_fields' => [
        'invoice.invoice_number',
        'invoice.issue_date',
        'company.kra_pin',
        'company.name',
        'totals.subtotal',
        'totals.grand_total',
    ],

    /*
    |--------------------------------------------------------------------------
    | Optional Fields
    |--------------------------------------------------------------------------
    |
    | Fields that are recommended but not required.
    | Missing optional fields will generate warnings but won't block export.
    |
    */
    'optional_fields' => [
        'invoice.due_date',
        'invoice.po_number',
        'client.kra_pin',
        'client.name',
        'totals.vat_amount',
        'invoice.currency',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    |
    | Default values to use when optional fields are missing.
    |
    */
    'defaults' => [
        'currency' => 'KES',
        'vatRate' => 16.00,
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Format Settings
    |--------------------------------------------------------------------------
    |
    | Settings for export file generation.
    |
    */
    'export' => [
        'date_format' => 'Y-m-d',
        'datetime_format' => 'Y-m-d\TH:i:s\Z',
        'decimal_places' => 2,
    ],
];
