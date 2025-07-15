<?php

namespace Hickr\Accounting\Tests\Console\Commands;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\JournalTemplate;
use Hickr\Accounting\Models\Tenant;
use Hickr\Accounting\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class PostRecurringJournalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_posts_due_recurring_journals()
    {
        $tenant = Tenant::factory()->create();

        $account = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => ChartOfAccount::TYPE_EXPENSE,
        ]);

        $template = JournalTemplate::create([
            'tenant_id' => $tenant->id,
            'name' => 'Rent',
            'description' => 'Monthly Rent',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'is_recurring' => true,
            'auto_post' => true,
            'frequency' => 'monthly',
            'start_date' => now()->subMonth()->toDateString(),
            'last_posted_at' => now()->subMonth()->subDay(),
        ]);

        $template->lines()->create([
            'tenant_id' => $tenant->id,
            'account_id' => $account->id,
            'type' => 'debit',
            'amount' => 1000,
        ]);

        // Now run command
        Artisan::call('accounting:post-recurring-journals');

        $this->assertDatabaseHas('journal_entries', [
            'tenant_id' => $tenant->id,
            'description' => 'Monthly Rent',
        ]);
    }
}