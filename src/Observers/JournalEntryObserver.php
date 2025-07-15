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

    public function updated(JournalEntry $entry): void
    {
        $changes = [];

        foreach ($entry->getChanges() as $field => $newValue) {
            if ($entry->isDirty($field)) {
                $changes[$field] = [
                    'old' => $entry->getOriginal($field),
                    'new' => $newValue,
                ];
            }
        }

        $action = match ($entry->status) {
            'pending_approval' => 'submitted',
            'approved' => 'approved',
            'rejected' => 'rejected',
            default => 'updated',
        };

        JournalEntryAudit::create([
            'journal_entry_id' => $entry->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'changes' => !empty($changes) ? $changes : null
        ]);
    }

    public function deleted(JournalEntry $entry): void
    {
        JournalEntryAudit::create([
            'journal_entry_id' => $entry->id,
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'changes' => null, // we don't need to store field-level changes for deletion
        ]);
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