<?php

Schema::table(config('accounting.tenant_table', 'tenants'), function (Blueprint $table) {
    $table->string('region_module')->default('global')->after('name');
    $table->string('base_currency')->default('MVR');
});