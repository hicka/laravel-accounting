<?php

namespace Hickr\Accounting\Models;

use Database\Factories\BillFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'vendor_id', 'reference', 'total', 'balance', 'currency_code', 'exchange_rate', 'date', 'due_date'
    ];

    protected static function newFactory()
    {
        return BillFactory::new();
    }

    public function lines()
    {
        return $this->hasMany(BillLine::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}