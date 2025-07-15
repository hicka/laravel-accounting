<?php

namespace Hickr\Accounting\Tests\Feature\Actions\Journals;

use Hickr\Accounting\Actions\Journals\SubmitJournalEntryAction;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\JournalEntryAudit;
use Hickr\Accounting\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubmitJournalEntryActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_marks_journal_entry_as_pending_approval()
    {
        $entry = JournalEntry::factory()->create([
            'status' => 'draft',
        ]);

        SubmitJournalEntryAction::execute($entry);

        $this->assertEquals('pending_approval', $entry->fresh()->status);

        $this->assertDatabaseHas('journal_entry_audits', [
            'journal_entry_id' => $entry->id,
            'action' => 'submitted',
        ]);
    }
}
