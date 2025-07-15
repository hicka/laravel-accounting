<?php
namespace Hickr\Accounting\Models;

use Database\Factories\CustomerCreditBalanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCreditBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'payment_id',
        'amount',
        'currency_code',
        'exchange_rate',
    ];

    protected static function newFactory()
    {
        return CustomerCreditBalanceFactory::new();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}