<?php
namespace Hickr\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalLine extends Model
{
    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    protected $fillable = [
      'journal_entry_id',
      'account_id',
      'tenant_id',
      'amount',
      'meta',
      'base_currency_amount',
      'type',
      'memo'
    ];

    public function entry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(\Hickr\Accounting\Models\JournalEntry::class);
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