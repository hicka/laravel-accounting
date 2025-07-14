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
    public const TYPE_GST_INPUT = 'gst_input';
    public const TYPE_GST_OUTPUT = 'gst_output';

    public const TAX_STANDARD = 'standard_gst';
    public const TAX_ZERO = 'zero_gst';
    public const TAX_EXEMPT = 'exempt';
    public const TAX_INPUT = 'input_tax';

    protected $fillable = [
        'tenant_id',
        'type',
        'tax_type'
    ];

    protected static function newFactory()
    {
        return ChartOfAccountFactory::new();
    }

    public function tenant()
    {
        return $this->belongsTo(config('accounting.tenant_model'));
    }
}
