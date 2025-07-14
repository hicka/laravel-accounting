<?php
namespace Hickr\Accounting\Tests\Modules\Mira\Reports;

use Hickr\Accounting\Tests\TestCase;
use Hickr\Accounting\Models\{Tenant, ChartOfAccount, JournalEntry};
use Hickr\Accounting\Modules\Mira\Reports\MiraGstSchedule3Report;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MiraGstSchedule3ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_output_tax_sales_correctly()
    {
        $tenant = Tenant::factory()->create();

        $gstOutputAccount = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => ChartOfAccount::TYPE_GST_OUTPUT,
            'name' => 'Output VAT Payable',
        ]);

        $entry = JournalEntry::create([
            'tenant_id' => $tenant->id,
            'date' => now()->toDateString(),
            'description' => 'Taxable Sale',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => 1060,
        ]);

        $entry->lines()->create([
            'tenant_id' => $tenant->id,
            'account_id' => $gstOutputAccount->id,
            'type' => 'credit',
            'amount' => 60,
            'base_currency_amount' => 60,
            'meta' => [
                'customer_name' => 'Altec Pvt Ltd',
                'invoice_number' => 'TAX-7788',
                'net_amount' => 1000,
                'total_amount' => 1060,
            ]
        ]);

        $report = (new MiraGstSchedule3Report)->generate([
            'tenant_id' => $tenant->id,
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->endOfMonth()->toDateString(),
        ]);

        $this->assertCount(1, $report);
        $this->assertEquals('Altec Pvt Ltd', $report[0]['customer_name']);
        $this->assertEquals('TAX-7788', $report[0]['invoice_number']);
        $this->assertEquals(60, $report[0]['gst_amount']);
        $this->assertEquals(1000, $report[0]['net_amount']);
        $this->assertEquals(1060, $report[0]['total_amount']);
    }
}