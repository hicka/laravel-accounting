<?php

namespace Hickr\Accounting\Tests\Feature\Actions\Journals;

use Hickr\Accounting\Actions\Journals\RejectJournalEntryAction;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\JournalEntryAudit;
use Hickr\Accounting\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RejectJournalEntryActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_marks_journal_entry_as_rejected()
    {
        $entry = JournalEntry::factory()->create([
            'status' => 'pending_approval',
        ]);

        RejectJournalEntryAction::execute($entry);

        $this->assertEquals('rejected', $entry->fresh()->status);

        $this->assertDatabaseHas('journal_entry_audits', [
            'journal_entry_id' => $entry->id,
            'action' => 'rejected',
        ]);
    }
}
