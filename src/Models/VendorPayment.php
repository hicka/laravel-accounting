<?php

namespace Hickr\Accounting\Models;

use Database\Factories\VendorPaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'vendor_id',
        'bill_id',
        'amount',
        'currency_code',
        'exchange_rate',
        'date',
        'notes',
    ];

    protected static function newFactory()
    {
        return VendorPaymentFactory::new();
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}