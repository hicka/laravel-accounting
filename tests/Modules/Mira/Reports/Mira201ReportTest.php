<?php

namespace Hickr\Accounting\Tests\Modules\Mira\Reports;

use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\Tenant;
use Hickr\Accounting\Modules\Mira\Reports\Mira201Report;
use Hickr\Accounting\Tests\TestCase;

class Mira201ReportTest extends TestCase
{
    public function test_it_generates_correct_mira_201_totals()
    {
        $tenant = Tenant::factory()->create();

        $standard = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id, 'type' => 'revenue', 'tax_type' => 'standard_gst']);
        $zero = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id, 'type' => 'revenue', 'tax_type' => 'zero_gst']);
        $exempt = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id, 'type' => 'revenue', 'tax_type' => 'exempt']);
        $input = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id, 'type' => 'expense', 'tax_type' => 'input_tax']);

        $entry = JournalEntry::create([
            'tenant_id' => $tenant->id,
            'date' => now()->toDateString(),
            'description' => 'Test MIRA 201',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => 40000,
        ]);

        $entry->lines()->createMany([
            ['tenant_id' => $tenant->id, 'account_id' => $standard->id, 'type' => 'credit', 'amount' => 10000, 'base_currency_amount' => 10000],
            ['tenant_id' => $tenant->id, 'account_id' => $zero->id, 'type' => 'credit', 'amount' => 5000, 'base_currency_amount' => 5000],
            ['tenant_id' => $tenant->id, 'account_id' => $exempt->id, 'type' => 'credit', 'amount' => 3000, 'base_currency_amount' => 3000],
            ['tenant_id' => $tenant->id, 'account_id' => $input->id, 'type' => 'debit', 'amount' => 8000, 'base_currency_amount' => 8000],
        ]);

        $report = (new Mira201Report())->generate([
            'tenant_id' => $tenant->id,
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->endOfMonth()->toDateString(),
        ]);

        $this->assertEquals(10000.0, $report['totals']['taxable_sales']);
        $this->assertEquals(5000.0, $report['totals']['zero_rated_sales']);
        $this->assertEquals(3000.0, $report['totals']['exempt_income']);
        $this->assertEquals(600.0, $report['totals']['output_tax']);
        $this->assertEquals(480.0, $report['totals']['input_tax']);
        $this->assertEquals(120.0, $report['net_gst_payable']);
    }
}
