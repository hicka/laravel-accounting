<?php

namespace Hickr\Accounting\Models;

use Illuminate\Database\Eloquent\Model;

class TenantConfig extends Model
{
    protected $table = 'tenant_configs';

    protected $fillable = [
        'tenant_id',
        'default_receivable_account_id',
        'default_payable_account_id',
        'default_cash_account_id',
        'default_sales_account_id',
        'default_expense_account_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}