<?php

namespace Hickr\Accounting\Tests\Actions;

use Hickr\Accounting\Actions\TrialBalanceReportAction;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\JournalLine;
use Hickr\Accounting\Models\Tenant;
use Hickr\Accounting\Tests\TestCase;

class TrialBalanceReportActionTest extends TestCase
{
    public function test_it_returns_flat_trial_balance_report()
    {
        $tenant = Tenant::factory()->create();
        $cash = ChartOfAccount::factory()->create(['type' => ChartOfAccount::TYPE_ASSET]);
        $sales = ChartOfAccount::factory()->create(['type' => ChartOfAccount::TYPE_REVENUE]);

        $entry = JournalEntry::create([
            'tenant_id' => $tenant->id,
            'date' => now()->toDateString(),
            'description' => 'Trial entry',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => 1000,
        ]);

        $entry->lines()->createMany([
            ['account_id' => $cash->id, 'type' => 'debit', 'amount' => 1000, 'tenant_id' => $tenant->id],
            ['account_id' => $sales->id, 'type' => 'credit', 'amount' => 1000, 'tenant_id' => $tenant->id],
        ]);

        $report = TrialBalanceReportAction::run([
            'tenant_id' => $tenant->id,
            'date_from' => now()->subMonth()->toDateString(),
            'date_to' => now()->toDateString(),
            'group_by_type' => false,
        ]);

        $this->assertCount(2, $report);
        $this->assertEquals(1000, $report[0]['debit'] ?: $report[1]['debit']);
        $this->assertEquals(1000, $report[1]['credit'] ?: $report[0]['credit']);
    }

    public function test_it_returns_grouped_trial_balance_report()
    {
        $tenant = Tenant::factory()->create();
        $cash = ChartOfAccount::factory()->create(['type' => ChartOfAccount::TYPE_ASSET]);
        $sales = ChartOfAccount::factory()->create(['type' => ChartOfAccount::TYPE_REVENUE]);

        $entry = JournalEntry::create([
            'tenant_id' => $tenant->id,
            'date' => now()->toDateString(),
            'description' => 'Trial entry',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => 1000,
        ]);

        $entry->lines()->createMany([
            ['account_id' => $cash->id, 'type' => 'debit', 'amount' => 1000, 'tenant_id' => $tenant->id],
            ['account_id' => $sales->id, 'type' => 'credit', 'amount' => 1000, 'tenant_id' => $tenant->id],
        ]);

        $report = TrialBalanceReportAction::run([
            'tenant_id' => $tenant->id,
            'group_by_type' => true,
        ]);



        $this->assertArrayHasKey('asset', $report);
        $this->assertArrayHasKey('revenue', $report);
    }
}