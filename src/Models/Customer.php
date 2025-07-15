<?php

namespace Hickr\Accounting\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('accounting.tables.customers', 'customers');
    }

    protected static function newFactory()
    {
        return CustomerFactory::new();
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }
}