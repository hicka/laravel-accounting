<?php
namespace Hickr\Accounting\Models;

use Illuminate\Database\Eloquent\Model;

class JournalLine extends Model
{
    protected $guarded = [];

    public function entry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function tenant()
    {
        return $this->belongsTo(config('accounting.tenant_model'));
    }
}