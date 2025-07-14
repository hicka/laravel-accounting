<?php

namespace Hickr\Accounting\Models;

use Database\Factories\ChartOfAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected static function newFactory()
    {
        return ChartOfAccountFactory::new();
    }

    public function tenant()
    {
        return $this->belongsTo(config('accounting.tenant_model'));
    }
}
