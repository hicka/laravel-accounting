<?php

namespace Hickr\Accounting\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $guarded = [];

    public function tenant()
    {
        return $this->belongsTo(config('accounting.tenant_model'));
    }
}
