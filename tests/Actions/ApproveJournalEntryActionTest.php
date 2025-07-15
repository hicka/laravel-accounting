<?php

namespace Hickr\Accounting\Tests\Actions;

use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Actions\ApproveJournalEntryAction;
use Hickr\Accounting\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\User;

class ApproveJournalEntryActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_approves_a_journal_entry()
    {
        //$this->actingAs((object)['id' => 1]);

        $entry = JournalEntry::factory()->create(['status' => 'pending_approval']);

        ApproveJournalEntryAction::execute($entry->refresh());

        $this->assertEquals('approved', $entry->fresh()->status);
        $this->assertEquals(null, $entry->fresh()->approved_by);
        $this->assertNotNull($entry->fresh()->approved_at);

        $this->assertDatabaseHas('journal_entry_audits', [
            'journal_entry_id' => $entry->id,
            'action' => 'approved',
        ]);
    }
}