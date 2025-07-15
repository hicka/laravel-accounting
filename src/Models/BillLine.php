<?php

namespace Hickr\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id', 'account_id', 'amount', 'description'
    ];

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}