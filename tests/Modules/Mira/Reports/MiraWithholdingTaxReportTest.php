<?php
namespace Hickr\Accounting\Tests\Modules\Mira\Reports;

use Hickr\Accounting\Models\{Tenant, ChartOfAccount, JournalEntry};
use Hickr\Accounting\Modules\Mira\Reports\MiraWithholdingTaxReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Hickr\Accounting\Tests\TestCase;

class MiraWithholdingTaxReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_wht_report_correctly()
    {
        $tenant = Tenant::factory()->create();

        $whtAccount = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'tax_type' => 'wht_professional_fees',
        ]);

        $entry = JournalEntry::create([
            'tenant_id' => $tenant->id,
            'date' => now(),
            'description' => 'Consulting payment',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => 10000,
        ]);

        $entry->lines()->create([
            'tenant_id' => $tenant->id,
            'account_id' => $whtAccount->id,
            'type' => 'credit',
            'amount' => 10000,
            'base_currency_amount' => 10000,
        ]);

        $report = (new MiraWithholdingTaxReport)->generate([
            'tenant_id' => $tenant->id,
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->endOfMonth()->toDateString(),
        ]);

        $this->assertCount(1, $report['lines']);
        $this->assertEquals(10000, $report['lines'][0]['amount']);
        $this->assertEquals(1000, $report['lines'][0]['withheld_amount']); // 10%
        $this->assertEquals(1000, $report['total_withheld']);
    }
}