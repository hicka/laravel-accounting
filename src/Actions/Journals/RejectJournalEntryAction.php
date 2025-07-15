<?php

namespace Hickr\Accounting\Actions\Journals;

use Hickr\Accounting\Models\JournalEntry;

class RejectJournalEntryAction
{
    public static function execute(JournalEntry $entry): void
    {
        $entry->update([
            'status' => 'rejected',
        ]);
        // Observer will log 'rejected'
    }
}