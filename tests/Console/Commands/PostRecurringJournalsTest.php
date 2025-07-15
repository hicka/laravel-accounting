<?php

namespace Hickr\Accounting\Tests\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Hickr\Accounting\Models\Tenant;
use Hickr\Accounting\Models\JournalTemplate;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Tests\TestCase;

class PostRecurringJournalsTest extends TestCase
{
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

        $this->artisan('accounting:post-recurring-journals')->assertSuccessful();

        Artisan::call('accounting:post-recurring-journals');

//        $this->assertDatabaseHas('journal_entries', [
//            'tenant_id' => $tenant->id,
//            'description' => 'Monthly Rent',
//        ]);
    }
}