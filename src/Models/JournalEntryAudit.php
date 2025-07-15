<?php

namespace Hickr\Accounting\Models;

use Database\Factories\JournalEntryAuditFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntryAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_entry_id',
        'user_id',
        'action',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    protected static function newFactory()
    {
        return JournalEntryAuditFactory::new();
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function user()
    {
        return $this->belongsTo(config('accounting.models.user', \App\Models\User::class));
    }
}