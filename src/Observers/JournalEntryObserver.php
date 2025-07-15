<?php

namespace Hickr\Accounting\Observers;

use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\JournalEntryAudit;
use Illuminate\Support\Facades\Auth;

class JournalEntryObserver
{
    public function created(JournalEntry $entry)
    {
        $this->log($entry, 'created');
    }

    public function updated(JournalEntry $entry)
    {
        $changes = [];

        foreach ($entry->getChanges() as $field => $newValue) {
            $changes[$field] = [
                'old' => $entry->getOriginal($field),
                'new' => $newValue,
            ];
        }

        JournalEntryAudit::create([
            'journal_entry_id' => $entry->id,
            'user_id' => auth()->id(),
            'action' => 'updated',
            'changes' => $changes,
        ]);
    }

    public function deleted(JournalEntry $entry)
    {
        $this->log($entry, 'deleted');
    }

    protected function log(JournalEntry $entry, string $action, array $changes = null)
    {
        JournalEntryAudit::create([
            'journal_entry_id' => $entry->id,
            'user_id' => Auth::id(), // null for system
            'action' => $action,
            'changes' => $changes ? json_encode($changes) : null,
        ]);
    }
}