<?php
namespace Hickr\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($entry) {
            $entry->reference = $entry->reference ?? \Str::uuid()->toString();
        });
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function tenant()
    {
        return $this->belongsTo(config('accounting.tenant_model'));
    }

    public function isBalanced(): bool
    {
        $debit = $this->lines()->where('side', 'debit')->sum('amount');
        $credit = $this->lines()->where('side', 'credit')->sum('amount');
        return bccomp((string) $debit, (string) $credit, 6) === 0;
    }
}