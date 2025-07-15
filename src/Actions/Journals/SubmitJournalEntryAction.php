<?php

namespace Hickr\Accounting\Actions\Journals;

use Hickr\Accounting\Models\JournalEntry;

class SubmitJournalEntryAction
{
    public static function execute(JournalEntry $entry): void
    {
        $entry->update([
            'status' => 'pending_approval',
        ]);
        // Observer will log 'submitted'
    }
}