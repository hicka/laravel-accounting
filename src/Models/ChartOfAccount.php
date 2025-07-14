<?php

namespace Hickr\Accounting\Models;

use Database\Factories\ChartOfAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ChartOfAccount extends Model
{
    use HasFactory;
    protected $guarded = [];

    public const TYPE_ASSET = 'asset';
    public const TYPE_LIABILITY = 'liability';
    public const TYPE_EQUITY = 'equity';
    public const TYPE_REVENUE = 'revenue';
    public const TYPE_EXPENSE = 'expense';

    protected static function newFactory()
    {
        return ChartOfAccountFactory::new();
    }

    public function tenant()
    {
        return $this->belongsTo(config('accounting.tenant_model'));
    }
}
