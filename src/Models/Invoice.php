<?php

namespace Hickr\Accounting\Models;

use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('accounting.tables.invoices', 'invoices');
    }

    protected static function newFactory()
    {
        return InvoiceFactory::new();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function lines()
    {
        return $this->hasMany(InvoiceLine::class, 'invoice_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    public function getBalanceAttribute()
    {
        return $this->total - $this->payments()->sum('amount');
    }
}