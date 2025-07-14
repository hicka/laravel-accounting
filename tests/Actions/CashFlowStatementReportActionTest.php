<?php

namespace Hickr\Accounting\Tests\Actions;

use Hickr\Accounting\Actions\CashFlowStatementReportAction;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\Tenant;
use Hickr\Accounting\Tests\TestCase;

class CashFlowStatementReportActionTest extends TestCase
{
    public function test_it_returns_cash_flow_breakdown_by_section()
    {
        $tenant = Tenant::factory()->create();

        $revenue = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => ChartOfAccount::TYPE_REVENUE,
        ]);

        $expense = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => ChartOfAccount::TYPE_EXPENSE,
        ]);

        $asset = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => ChartOfAccount::TYPE_ASSET,
        ]);

        $liability = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => ChartOfAccount::TYPE_LIABILITY,
        ]);

        $entry = JournalEntry::create([
            'tenant_id' => $tenant->id,
            'date' => now()->toDateString(),
            'description' => 'Cash movements',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => 20000,
        ]);

        $entry->lines()->createMany([
            ['tenant_id' => $tenant->id, 'account_id' => $revenue->id, 'type' => 'credit', 'amount' => 5000, 'base_currency_amount' => 5000],
            ['tenant_id' => $tenant->id, 'account_id' => $expense->id, 'type' => 'debit', 'amount' => 2000, 'base_currency_amount' => 2000],
            ['tenant_id' => $tenant->id, 'account_id' => $asset->id, 'type' => 'debit', 'amount' => 7000, 'base_currency_amount' => 7000],
            ['tenant_id' => $tenant->id, 'account_id' => $liability->id, 'type' => 'credit', 'amount' => 6000, 'base_currency_amount' => 6000],
        ]);

        $report = CashFlowStatementReportAction::run([
            'tenant_id' => $tenant->id,
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->endOfMonth()->toDateString(),
        ]);

        $this->assertSame(3000.0, (float) $report['cash_flows']['operating']);
        $this->assertSame(-7000.0, (float) $report['cash_flows']['investing']);
        $this->assertSame(6000.0, (float) $report['cash_flows']['financing']);
        $this->assertSame(2000.0, (float) $report['net_cash_flow']);
    }
}