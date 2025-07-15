<?php

namespace Hickr\Accounting\Tests\Actions\Vendors;

use Hickr\Accounting\Actions\Vendors\CreateBillAction;
use Hickr\Accounting\Models\Bill;
use Hickr\Accounting\Models\TenantConfig;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\Vendor;
use Hickr\Accounting\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateBillActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_vendor_bill_with_journal_entry()
    {
        $tenant = \Hickr\Accounting\Models\Tenant::factory()->create();
        $vendor = Vendor::factory()->create(['tenant_id' => $tenant->id]);

        $expenseAccount1 = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => 'expense',
        ]);

        $expenseAccount2 = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => 'expense',
        ]);

        $payableAccount = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => 'liability',
        ]);

        TenantConfig::create([
            'tenant_id' => $tenant->id,
            'default_payable_account_id' => $payableAccount->id,
        ]);

        $data = [
            'tenant_id' => $tenant->id,
            'vendor_id' => $vendor->id,
            'total' => 500.00,
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'lines' => [
                [
                    'account_id' => $expenseAccount1->id,
                    'amount' => 300,
                    'description' => 'Office supplies',
                ],
                [
                    'account_id' => $expenseAccount2->id,
                    'amount' => 200,
                    'description' => 'Cleaning services',
                ],
            ],
        ];

        $bill = CreateBillAction::execute($data);

        $this->assertInstanceOf(Bill::class, $bill);
        $this->assertEquals(500.00, $bill->total);
        $this->assertCount(2, $bill->lines);
        $this->assertDatabaseHas('journal_entries', [
            'tenant_id' => $tenant->id,
            'description' => 'Vendor Bill #' . $bill->id,
        ]);
        $this->assertDatabaseCount('journal_lines', 3);
    }
}