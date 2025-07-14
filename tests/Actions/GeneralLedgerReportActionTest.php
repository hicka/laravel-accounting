<?php

namespace Hickr\Accounting\Tests\Actions;

use Hickr\Accounting\Actions\GeneralLedgerReportAction;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\Tenant;
use Hickr\Accounting\Tests\TestCase;

class GeneralLedgerReportActionTest extends TestCase
{
    public function test_it_returns_general_ledger_grouped_by_account()
    {
        $tenant = Tenant::factory()->create();

        $cash = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => ChartOfAccount::TYPE_ASSET,
            'name' => 'Cash',
        ]);

        $sales = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => ChartOfAccount::TYPE_REVENUE,
            'name' => 'Sales',
        ]);

        $entry = JournalEntry::create([
            'tenant_id' => $tenant->id,
            'date' => now()->toDateString(),
            'description' => 'Sale',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => 1000,
        ]);

        $entry->lines()->createMany([
            [
                'account_id' => $cash->id,
                'type' => 'debit',
                'amount' => 1000,
                'base_currency_amount' => 1000,
                'tenant_id' => $tenant->id,
            ],
            [
                'account_id' => $sales->id,
                'type' => 'credit',
                'amount' => 1000,
                'base_currency_amount' => 1000,
                'tenant_id' => $tenant->id,
            ],
        ]);

        $report = GeneralLedgerReportAction::run([
            'tenant_id' => $tenant->id,
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->endOfMonth()->toDateString(),
        ]);

        $this->assertCount(2, $report);

        $cashLedger = collect($report)->firstWhere('account_id', $cash->id);
        $this->assertEquals('Cash', $cashLedger['account_name']);
        $this->assertEquals(1000, $cashLedger['total_debit']);
        $this->assertEquals(0, $cashLedger['total_credit']);
        $this->assertCount(1, $cashLedger['entries']);

        $salesLedger = collect($report)->firstWhere('account_id', $sales->id);
        $this->assertEquals('Sales', $salesLedger['account_name']);
        $this->assertEquals(0, $salesLedger['total_debit']);
        $this->assertEquals(1000, $salesLedger['total_credit']);
        $this->assertCount(1, $salesLedger['entries']);
    }
}