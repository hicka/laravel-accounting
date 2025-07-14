<?php
namespace Hickr\Accounting\Tests\Modules\Mira\Reports;

use Hickr\Accounting\Tests\TestCase;
use Hickr\Accounting\Models\{Tenant, ChartOfAccount, JournalEntry};
use Hickr\Accounting\Modules\Mira\Reports\MiraIncomeTaxReport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MiraIncomeTaxReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_income_tax_report_correctly()
    {
        $tenant = Tenant::factory()->create();

        $revenue = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => 'revenue',
        ]);

        $cogs = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => 'cost_of_sales',
        ]);

        $expense = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => 'expense',
        ]);

        $nonTaxable = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => 'non_taxable',
        ]);

        $entry = JournalEntry::create([
            'tenant_id' => $tenant->id,
            'date' => now()->toDateString(),
            'description' => 'Monthly summary',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => 50000,
        ]);

        $entry->lines()->createMany([
            ['tenant_id' => $tenant->id, 'account_id' => $revenue->id, 'type' => 'credit', 'amount' => 30000, 'base_currency_amount' => 30000],
            ['tenant_id' => $tenant->id, 'account_id' => $cogs->id, 'type' => 'debit', 'amount' => 10000, 'base_currency_amount' => 10000],
            ['tenant_id' => $tenant->id, 'account_id' => $expense->id, 'type' => 'debit', 'amount' => 5000, 'base_currency_amount' => 5000],
            ['tenant_id' => $tenant->id, 'account_id' => $nonTaxable->id, 'type' => 'credit', 'amount' => 2000, 'base_currency_amount' => 2000],
        ]);

        $report = (new MiraIncomeTaxReport)->generate([
            'tenant_id' => $tenant->id,
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->endOfMonth()->toDateString(),
        ]);

        $this->assertEquals(30000, $report['revenue']);
        $this->assertEquals(10000, $report['direct_expense']);
        $this->assertEquals(5000, $report['operating_expense']);
        $this->assertEquals(2000, $report['non_taxable_income']);
        $this->assertEquals(20000, $report['gross_profit']); // 30k - 10k
        $this->assertEquals(15000, $report['net_profit']);   // 20k - 5k
        $this->assertEquals(13000, $report['taxable_profit']); // 15k - 2k
    }
}