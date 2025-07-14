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
        $entry = PostJournalEntryAction::execute([
            'tenant' => $this->tenant,
            'date' => now()->toDateString(),
            'description' => 'Capital injection',
            'lines' => [
                ['account_id' => 1, 'amount' => '1000.00', 'side' => 'debit'],
                ['account_id' => 2, 'amount' => '1000.00', 'side' => 'credit'],
            ],
        ]);

        $this->assertInstanceOf(JournalEntry::class, $entry);
        $this->assertCount(2, $entry->lines);
        $this->assertEquals('1000.000000', $entry->lines[0]->amount);
    }

    /** @test */
    public function it_converts_foreign_currency_to_base_and_posts()
    {
        $entry = PostJournalEntryAction::execute([
            'tenant_id' => $this->tenant->id,
            'date' => now()->toDateString(),
            'description' => 'USD transaction',
            'lines' => [
                [
                    'account_id' => 1,
                    'amount' => '100.00',
                    'side' => 'debit',
                    'currency_code' => 'USD',
                    'exchange_rate' => 15.0,
                    'inverse' => false,
                ],
                [
                    'account_id' => 2,
                    'amount' => '1500.00',
                    'side' => 'credit',
                    'currency_code' => 'MVR',
                ],
            ],
        ]);

        $this->assertEquals('1500.000000', $entry->lines[0]->amount); // USD converted to MVR
        $this->assertEquals('USD', $entry->lines[0]->currency_code);
    }

    /** @test */
    public function it_throws_exception_when_unbalanced()
    {
        $this->expectException(UnbalancedJournalException::class);

        PostJournalEntryAction::execute([
            'tenant_id' => $this->tenant->id,
            'date' => now()->toDateString(),
            'description' => 'Broken entry',
            'lines' => [
                ['account_id' => 1, 'amount' => '1000.00', 'side' => 'debit'],
                ['account_id' => 2, 'amount' => '900.00', 'side' => 'credit'],
            ],
        ]);
    }
}