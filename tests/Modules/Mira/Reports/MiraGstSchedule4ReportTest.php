<?php
namespace Hickr\Accounting\Tests\Modules\Mira\Reports;

use Hickr\Accounting\Tests\TestCase;
use Hickr\Accounting\Models\{Tenant, ChartOfAccount, JournalEntry};
use Hickr\Accounting\Modules\Mira\Reports\MiraGstSchedule4Report;
use Illuminate\Foundation\Testing\RefreshDatabase;


class MiraGstSchedule4ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_zero_rated_and_exempt_sales()
    {
        $tenant = Tenant::factory()->create();

        $revenue = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => ChartOfAccount::TYPE_REVENUE,
        ]);

        $entry = JournalEntry::create([
            'tenant_id' => $tenant->id,
            'date' => now()->toDateString(),
            'description' => 'Schedule 4 sales',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => 8000,
        ]);

        $entry->lines()->createMany([
            [
                'tenant_id' => $tenant->id,
                'account_id' => $revenue->id,
                'type' => 'credit',
                'amount' => 5000,
                'base_currency_amount' => 5000,
                'meta' => ['gst_type' => 'zero_rated'],
            ],
            [
                'tenant_id' => $tenant->id,
                'account_id' => $revenue->id,
                'type' => 'credit',
                'amount' => 3000,
                'base_currency_amount' => 3000,
                'meta' => ['gst_type' => 'exempt'],
            ],
        ]);

        $report = (new MiraGstSchedule4Report)->generate([
            'tenant_id' => $tenant->id,
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->endOfMonth()->toDateString(),
        ]);

        $this->assertCount(2, $report);
        $this->assertEquals('zero_rated', $report[0]['income_type']);
        $this->assertEquals(5000, $report[0]['amount']);
        $this->assertEquals('exempt', $report[1]['income_type']);
        $this->assertEquals(3000, $report[1]['amount']);
    }
}