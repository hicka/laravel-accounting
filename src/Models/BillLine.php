<?php

namespace Hickr\Accounting\Models;

use Database\Factories\BillLineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillLine extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return BillLineFactory::new();
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}