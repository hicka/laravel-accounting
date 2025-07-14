<?php

namespace Hickr\Accounting\Tests\Modules\Mira\Reports;

use Hickr\Accounting\Tests\TestCase;
use Hickr\Accounting\Models\{Tenant, ChartOfAccount, JournalEntry};
use Hickr\Accounting\Modules\Mira\Reports\MiraGstSchedule5Report;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MiraGstSchedule5ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_expense_lines_with_valid_gst_meta()
    {
        $tenant = Tenant::factory()->create();

        $expense = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => ChartOfAccount::TYPE_EXPENSE,
        ]);

        $entry = JournalEntry::create([
            'tenant_id' => $tenant->id,
            'date' => now()->toDateString(),
            'description' => 'Schedule 5 purchase',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => 1060,
        ]);

        $entry->lines()->create([
            'tenant_id' => $tenant->id,
            'account_id' => $expense->id,
            'type' => 'debit',
            'amount' => 1060,
            'base_currency_amount' => 1060,
            'meta' => [
                'supplier_name' => 'Island Mart',
                'supplier_tin' => '1010101GST001',
                'invoice_number' => 'PUR-7788',
                'net_amount' => 1000,
                'gst_amount' => 60,
            ],
        ]);

        $report = (new MiraGstSchedule5Report)->generate([
            'tenant_id' => $tenant->id,
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->endOfMonth()->toDateString(),
        ]);

        $this->assertCount(1, $report);
        $this->assertEquals('Island Mart', $report[0]['supplier_name']);
        $this->assertEquals('1010101GST001', $report[0]['supplier_tin']);
        $this->assertEquals('PUR-7788', $report[0]['invoice_number']);
        $this->assertEquals(1000.0, $report[0]['net_amount']);
        $this->assertEquals(60.0, $report[0]['gst_amount']);
        $this->assertEquals(1060.0, $report[0]['total_amount']);
    }
}