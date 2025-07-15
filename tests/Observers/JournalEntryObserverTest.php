<?php

namespace Hickr\Accounting\Tests\Observers;

use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\Tenant;
use Hickr\Accounting\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JournalEntryObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_audit_on_journal_entry_creation()
    {
        $tenant = Tenant::factory()->create();
        $account = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id]);

        $entry = JournalEntry::create([
            'tenant_id' => $tenant->id,
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => 1000,
            'description' => 'Test Entry',
            'date' => now()->toDateString(),
        ]);

        $entry->lines()->create([
            'tenant_id' => $tenant->id,
            'account_id' => $account->id,
            'type' => 'debit',
            'amount' => 1000,
            'base_currency_amount' => 1000,
            'currency_code' => 'MVR',
        ]);

        $this->assertDatabaseHas('journal_entry_audits', [
            'journal_entry_id' => $entry->id,
            'action' => 'created',
        ]);
    }

    public function test_it_logs_updated_journal_entry()
    {
        $tenant = Tenant::factory()->create();

        $entry = JournalEntry::factory()->create([
            'tenant_id' => $tenant->id,
            'description' => 'Initial Desc',
        ]);

        $entry->update(['description' => 'Updated Desc']);

        $this->assertDatabaseHas('journal_entry_audits', [
            'journal_entry_id' => $entry->id,
            'action' => 'updated',
        ]);

        $audit = \Hickr\Accounting\Models\JournalEntryAudit::where('journal_entry_id', $entry->id)
            ->where('action', 'updated')
            ->first();

        $changes = $audit->changes;

        $this->assertEquals('Initial Desc', $changes['description']['old']);
        $this->assertEquals('Updated Desc', $changes['description']['new']);
    }
}