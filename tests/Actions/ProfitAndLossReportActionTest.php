<?php

namespace Hickr\Accounting\Tests\Actions;

use Hickr\Accounting\Actions\ProfitAndLossReportAction;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\Tenant;
use Hickr\Accounting\Tests\TestCase;

class ProfitAndLossReportActionTest extends TestCase
{
    public function test_it_returns_flat_profit_and_loss_report()
    {
        $tenant = Tenant::factory()->create();

        $sales = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => 'revenue',
        ]);

        $rent = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => 'expense',
        ]);

        $entry = JournalEntry::create([
            'tenant_id' => $tenant->id,
            'date' => now()->toDateString(),
            'description' => 'P&L Test',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => 1000,
        ]);

        $entry->lines()->createMany([
            ['tenant_id' => $tenant->id, 'account_id' => $sales->id, 'type' => 'credit', 'amount' => 2000, 'base_currency_amount' => 2000],
            ['tenant_id' => $tenant->id, 'account_id' => $rent->id, 'type' => 'debit', 'amount' => 500, 'base_currency_amount' => 500],
        ]);

        $report = ProfitAndLossReportAction::run([
            'tenant_id' => $tenant->id,
            'date_from' => now()->subMonth()->toDateString(),
            'date_to' => now()->toDateString(),
            'group_by_account' => false,
        ]);

        $this->assertEquals(2000, $report['revenue']);
        $this->assertEquals(500, $report['expenses']);
        $this->assertEquals(1500, $report['net_profit']);
    }

    public function test_it_returns_grouped_profit_and_loss_report()
    {
        $tenant = Tenant::factory()->create();

        $sales = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Sales',
            'type' => 'revenue',
        ]);

        $marketing = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Marketing',
            'type' => 'expense',
        ]);

        $entry = JournalEntry::create([
            'tenant_id' => $tenant->id,
            'date' => now()->toDateString(),
            'description' => 'Grouped P&L',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => 1000,
        ]);

        $entry->lines()->createMany([
            ['tenant_id' => $tenant->id, 'account_id' => $sales->id, 'type' => 'credit', 'amount' => 1000, 'base_currency_amount' => 1000],
            ['tenant_id' => $tenant->id, 'account_id' => $marketing->id, 'type' => 'debit', 'amount' => 400, 'base_currency_amount' => 400],
        ]);

        $report = ProfitAndLossReportAction::run([
            'tenant_id' => $tenant->id,
            'date_from' => now()->subMonth()->toDateString(),
            'date_to' => now()->toDateString(),
            'group_by_account' => true,
        ]);

        $this->assertCount(1, $report['revenue']);
        $this->assertEquals('Sales', $report['revenue'][0]['name']);
        $this->assertEquals(1000, $report['revenue'][0]['amount']);

        $this->assertCount(1, $report['expenses']);
        $this->assertEquals('Marketing', $report['expenses'][0]['name']);
        $this->assertEquals(400, $report['expenses'][0]['amount']);

        $this->assertEquals(600, $report['net_profit']);
    }
}