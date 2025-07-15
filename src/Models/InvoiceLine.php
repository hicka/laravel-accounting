<?php

namespace Hickr\Accounting\Models;

use Database\Factories\InvoiceLineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('accounting.tables.invoice_lines', 'invoice_lines');
    }

    protected static function newFactory()
    {
        return InvoiceLineFactory::new();
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}