<?php
namespace Hickr\Accounting\Tests\Modules\Mira\Reports;

use Hickr\Accounting\Tests\TestCase;
use Hickr\Accounting\Models\{Tenant, ChartOfAccount, JournalEntry};
use Hickr\Accounting\Modules\Mira\Reports\MiraGstSchedule2Report;
use Illuminate\Foundation\Testing\RefreshDatabase;


class MiraGstSchedule2ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_input_tax_purchases_correctly()
    {
        $tenant = Tenant::factory()->create();

        $gstInputAccount = ChartOfAccount::factory()->gstInput()->create([
            'tenant_id' => $tenant->id,
            'name' => 'VAT Receivable'
        ]);

        $entry = JournalEntry::create([
            'tenant_id' => $tenant->id,
            'date' => now()->toDateString(),
            'description' => 'Input tax claim',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => 600,
        ]);

        $entry->lines()->create([
            'tenant_id' => $tenant->id,
            'account_id' => $gstInputAccount->id,
            'type' => 'debit',
            'amount' => 60,
            'base_currency_amount' => 60,
            'meta' => [
                'supplier_name' => 'Island Stationery Co.',
                'invoice_number' => 'INV-3344',
                'net_amount' => 1000,
                'total_amount' => 1060,
            ]
        ]);

        $report = (new MiraGstSchedule2Report)->generate([
            'tenant_id' => $tenant->id,
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->endOfMonth()->toDateString(),
        ]);

        $this->assertCount(1, $report);
        $this->assertEquals('Island Stationery Co.', $report[0]['supplier_name']);
        $this->assertEquals('INV-3344', $report[0]['invoice_number']);
        $this->assertEquals(60, $report[0]['gst_amount']);
        $this->assertEquals(1000, $report[0]['net_amount']);
        $this->assertEquals(1060, $report[0]['total_amount']);
    }
}