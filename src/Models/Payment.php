<?php

namespace Hickr\Accounting\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('accounting.tables.payments', 'payments');
    }

    protected static function newFactory()
    {
        return PaymentFactory::new();
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}