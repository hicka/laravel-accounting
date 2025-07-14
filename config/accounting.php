<?php

return [
    'multi_currency' => true,
    'multi_tenant' => true,
    'default_currency' => 'MVR',
    'region_module' => env('ACCOUNTING_REGION', 'global'), // mira, global, etc.

    'tenant_model' => \Hickr\Accounting\Models\Tenant::class,

    // Specify the column used to determine regional compliance
    'region_module_column' => 'region_module',

    // Optionally, if your tenant model uses a custom table
    'tenant_table' => 'tenants',

    'cash_flow_map' => [
        'operating' => ['revenue', 'expense'],
        'investing' => ['asset'],
        'financing' => ['equity', 'liability'],
    ],

    'tax_types' => [
        'standard_gst',
        'zero_gst',
        'exempt',
        'input_tax',
    ],

    'modules' => [
        'mira' => [
            'gst_rates' => [
                'standard' => 0.06,
                'input' => 0.06,
            ],
            'wht_rates' => [
                'wht_professional_fees' => 0.10,
                'wht_rent' => 0.05,
                'wht_contract_services' => 0.03,
            ],
            'income_tax' => [
                'adjustments' => [], // custom logic (TBD)
            ],
            'enable_fixed_asset_reporting' => true,
        ],
    ],
];