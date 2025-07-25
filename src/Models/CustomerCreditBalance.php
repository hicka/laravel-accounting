<?php
namespace Hickr\Accounting\Models;

use Database\Factories\CustomerCreditBalanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCreditBalance extends Model
{
    use HasFactory;

   protected $guarded = [];

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

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency_code;
    }

    public function getFormattedBaseAmountAttribute(): string
    {
        return number_format($this->base_currency_amount, 2) . ' (base)';
    }
}