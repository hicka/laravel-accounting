<?php

namespace Hickr\Accounting\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCreditRefund extends Model
{
    protected $fillable = [
        'tenant_id', 'customer_id', 'credit_balance_id', 'journal_entry_id',
        'amount', 'currency_code', 'exchange_rate', 'base_currency_amount',
        'refund_method', 'date',
    ];

    public function creditBalance()
    {
        return $this->belongsTo(CustomerCreditBalance::class, 'credit_balance_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }
}