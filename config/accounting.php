<?php

return [
    'multi_currency' => true,
    'multi_tenant' => true,
    'default_currency' => 'MVR',
    'region_module' => env('ACCOUNTING_REGION', 'global'), // mira, global, etc.

    'tenant_model' => App\Models\Tenant::class,

    // Specify the column used to determine regional compliance
    'region_module_column' => 'region_module',

    // Optionally, if your tenant model uses a custom table
    'tenant_table' => 'tenants',
];