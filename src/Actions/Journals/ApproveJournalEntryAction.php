<?php

namespace Hickr\Accounting\Actions\Journals;

use Hickr\Accounting\Models\JournalEntry;

class ApproveJournalEntryAction
{
    public static function execute(JournalEntry $entry): JournalEntry
    {
        $user = auth()->user();
        $userId = $user?->id;

        $entry->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return $entry;
    }
}