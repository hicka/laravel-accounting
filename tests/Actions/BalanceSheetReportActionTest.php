<?php

namespace Hickr\Accounting\Tests\Actions;

use Hickr\Accounting\Actions\BalanceSheetReportAction;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\Tenant;
use Hickr\Accounting\Tests\TestCase;

class BalanceSheetReportActionTest extends TestCase
{
    public function test_it_returns_flat_balance_sheet()
    {
        $tenant = Tenant::factory()->create();

        $cash = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => 'asset',
        ]);

        $payable = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => 'liability',
        ]);

        $capital = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => 'equity',
        ]);

        $entry = JournalEntry::create([
            'tenant_id' => $tenant->id,
            'date' => now()->toDateString(),
            'description' => 'Initial balance',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => 1000,
        ]);

        $entry->lines()->createMany([
            ['account_id' => $cash->id, 'type' => 'debit', 'amount' => 10000, 'base_currency_amount' => 10000, 'tenant_id' => $tenant->id],
            ['account_id' => $payable->id, 'type' => 'credit', 'amount' => 4000, 'base_currency_amount' => 4000, 'tenant_id' => $tenant->id],
            ['account_id' => $capital->id, 'type' => 'credit', 'amount' => 6000, 'base_currency_amount' => 6000, 'tenant_id' => $tenant->id],
        ]);

        $report = BalanceSheetReportAction::run([
            'tenant_id' => $tenant->id,
            'date_to' => now()->toDateString(),
            'group_by_account' => false,
        ]);

        $this->assertEquals(10000, $report['assets']);
        $this->assertEquals(4000, $report['liabilities']);
        $this->assertEquals(6000, $report['equity']);
        $this->assertEquals(10000, $report['totals']['assets']);
        $this->assertEquals(10000, $report['totals']['liabilities'] + $report['totals']['equity']);
    }

    public function test_it_returns_grouped_balance_sheet()
    {
        $tenant = Tenant::factory()->create();

        $cash = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => 'asset',
            'name' => 'Cash',
        ]);

        $loan = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => 'liability',
            'name' => 'Loan',
        ]);

        $retained = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => 'equity',
            'name' => 'Retained Earnings',
        ]);

        $entry = JournalEntry::create([
            'tenant_id' => $tenant->id,
            'date' => now()->toDateString(),
            'description' => 'Opening',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => 1000,
        ]);

        $entry->lines()->createMany([
            ['account_id' => $cash->id, 'type' => 'debit', 'amount' => 7000, 'base_currency_amount' => 7000, 'tenant_id' => $tenant->id],
            ['account_id' => $loan->id, 'type' => 'credit', 'amount' => 2000, 'base_currency_amount' => 2000, 'tenant_id' => $tenant->id],
            ['account_id' => $retained->id, 'type' => 'credit', 'amount' => 5000, 'base_currency_amount' => 5000, 'tenant_id' => $tenant->id],
        ]);

        $report = BalanceSheetReportAction::run([
            'tenant_id' => $tenant->id,
            'date_to' => now()->toDateString(),
            'group_by_account' => true,
        ]);

        $this->assertCount(1, $report['assets']);
        $this->assertEquals('Cash', $report['assets'][0]['name']);
        $this->assertEquals(7000, $report['assets'][0]['amount']);

        $this->assertCount(1, $report['liabilities']);
        $this->assertEquals('Loan', $report['liabilities'][0]['name']);
        $this->assertEquals(2000, $report['liabilities'][0]['amount']);

        $this->assertCount(1, $report['equity']);
        $this->assertEquals('Retained Earnings', $report['equity'][0]['name']);
        $this->assertEquals(5000, $report['equity'][0]['amount']);

        $this->assertEquals(7000, $report['totals']['assets']);
        $this->assertEquals(7000, $report['totals']['liabilities'] + $report['totals']['equity']);
    }
}