<?php

namespace Hickr\Accounting\Tests\Console\Commands;

use Hickr\Accounting\Actions\PostRecurringJournalTemplateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Hickr\Accounting\Models\Tenant;
use Hickr\Accounting\Models\JournalTemplate;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class PostRecurringJournalsTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Force-register command to ensure it's booted during the test runtime
        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)
            ->registerCommand(new \Hickr\Accounting\Console\Commands\PostRecurringJournals);
    }

    public function test_it_posts_due_recurring_journals()
    {
        $tenant = Tenant::factory()->create();

        $debitAccount = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => ChartOfAccount::TYPE_EXPENSE,
        ]);

        $creditAccount = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => ChartOfAccount::TYPE_LIABILITY,
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
            'last_posted_at' => null,
        ]);

        $template->lines()->createMany([
            [
                'template_id' => $template->id,
                'tenant_id' => $tenant->id,
                'account_id' => $debitAccount->id,
                'type' => 'debit',
                'amount' => 1000,
            ],
            [
                'template_id' => $template->id,
                'tenant_id' => $tenant->id,
                'account_id' => $creditAccount->id,
                'type' => 'credit',
                'amount' => 1000,
            ],
        ]);

        PostRecurringJournalTemplateAction::execute($template);

        $this->assertDatabaseHas('journal_entries', [
            'tenant_id' => $tenant->id,
            'description' => 'Monthly Rent',
        ]);

    }
}