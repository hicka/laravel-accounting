<?php

namespace Hickr\Accounting\Tests\Actions;

use Hickr\Accounting\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Hickr\Accounting\Actions\PostJournalEntryAction;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\JournalLine;
use Hickr\Accounting\Exceptions\UnbalancedJournalException;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;


class PostJournalEntryActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up schema and run migrations manually
        foreach (glob(__DIR__ . '/../../database/migrations/*.php') as $filename) {
            include_once $filename;
            (require $filename)->up();
        }
        // Run package migrations
//        $this->artisan('migrate', ['--database' => 'sqlite']);

        // Create tenant and accounts
        $this->tenant = Tenant::factory()->create(['base_currency' => 'MVR']);

        ChartOfAccount::create(['tenant_id' => $this->tenant->id, 'code' => '1000', 'name' => 'Cash', 'type' => 'asset']);
        ChartOfAccount::create(['tenant_id' => $this->tenant->id, 'code' => '3000', 'name' => 'Equity', 'type' => 'equity']);
    }

    /** @test */
    public function test_it_posts_a_balanced_single_currency_journal_entry()
    {
        $tenant = Tenant::factory()->create([
            'region_module' => 'mira',
            'base_currency' => 'MVR',
        ]);

        $cash = ChartOfAccount::factory()->create();
        $sales = ChartOfAccount::factory()->create();

        $data = [
            'tenant' => $tenant,
            'tenant_id' => $tenant->id,
            'date' => now()->format('Y-m-d'),
            'description' => 'Test Sale Entry',
            'currency_code' => 'MVR',           // ✅ added
            'exchange_rate' => 1,               // ✅ added
            'lines' => [
                ['account_id' => $cash->id, 'amount' => 1000, 'type' => 'debit'],
                ['account_id' => $sales->id, 'amount' => 1000, 'type' => 'credit'],
            ],
        ];

        $entry = PostJournalEntryAction::execute($data);

        $this->assertInstanceOf(JournalEntry::class, $entry);
        $this->assertEquals('MVR', $entry->currency_code);
        $this->assertEquals(1000, $entry->base_currency_amount);
        $this->assertCount(2, $entry->lines);
    }

    /** @test */
    public function test_it_converts_foreign_currency_to_base_and_posts()
    {
        $tenant = \Hickr\Accounting\Models\Tenant::factory()->create([
            'base_currency' => 'MVR',
        ]);

        $debitAccount = \Hickr\Accounting\Models\ChartOfAccount::factory()->create();
        $creditAccount = \Hickr\Accounting\Models\ChartOfAccount::factory()->create();

        $entry = \Hickr\Accounting\Actions\PostJournalEntryAction::execute([
            'tenant_id' => $tenant->id,
            'date' => now()->toDateString(),
            'description' => 'USD transaction',
            'currency_code' => 'USD',             // Main currency for the journal
            'exchange_rate' => 15.0,              // USD → MVR
            'lines' => [
                [
                    'account_id' => $debitAccount->id,
                    'amount' => 100.00,
                    'type' => 'debit',
                ],
                [
                    'account_id' => $creditAccount->id,
                    'amount' => 100.00,
                    'type' => 'credit',
                ],
            ],
        ]);

        $this->assertEquals('USD', $entry->currency_code);
        $this->assertEquals(1500.00, $entry->base_currency_amount); // 100 USD * 15.0
        $this->assertCount(2, $entry->lines);
    }

    /** @test */
    public function test_it_throws_exception_when_unbalanced()
    {
        $this->expectException(UnbalancedJournalException::class);

        $tenant = \Hickr\Accounting\Models\Tenant::factory()->create([
            'base_currency' => 'MVR',
        ]);

        $debitAccount = \Hickr\Accounting\Models\ChartOfAccount::factory()->create();
        $creditAccount = \Hickr\Accounting\Models\ChartOfAccount::factory()->create();

        \Hickr\Accounting\Actions\PostJournalEntryAction::execute([
            'tenant_id' => $tenant->id,
            'date' => now()->toDateString(),
            'description' => 'Broken entry',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'lines' => [
                ['account_id' => $debitAccount->id, 'amount' => 1000.00, 'type' => 'debit'],
                ['account_id' => $creditAccount->id, 'amount' => 900.00, 'type' => 'credit'],
            ],
        ]);
    }
}