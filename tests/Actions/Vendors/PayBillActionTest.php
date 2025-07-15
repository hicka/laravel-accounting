<?php

namespace Hickr\Accounting\Tests\Actions\Vendors;

use Hickr\Accounting\Actions\Vendors\PayBillAction;
use Hickr\Accounting\Models\Tenant;
use Hickr\Accounting\Models\Vendor;
use Hickr\Accounting\Models\Bill;
use Hickr\Accounting\Models\BillLine;
use Hickr\Accounting\Models\VendorPayment;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\TenantConfig;
use Hickr\Accounting\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayBillActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_pays_full_bill_amount(): void
    {
        $tenant = Tenant::factory()->create();
        $vendor = Vendor::factory()->create(['tenant_id' => $tenant->id]);

        $cash = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => ChartOfAccount::TYPE_ASSET,
        ]);

        $payable = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => ChartOfAccount::TYPE_LIABILITY,
        ]);

        TenantConfig::create([
            'tenant_id' => $tenant->id,
            'default_cash_account_id' => $cash->id,
            'default_payable_account_id' => $payable->id,
        ]);

        $bill = Bill::factory()->create([
            'tenant_id' => $tenant->id,
            'vendor_id' => $vendor->id,
            'total' => 2000,
            'balance' => 2000,
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
        ]);

        BillLine::factory()->create([
            'bill_id' => $bill->id,
            'tenant_id' => $tenant->id,
            'account_id' => ChartOfAccount::factory()->create(['tenant_id' => $tenant->id])->id,
            'amount' => 2000,
        ]);

        $payment = PayBillAction::execute([
            'tenant_id' => $tenant->id,
            'vendor_id' => $vendor->id,
            'bill' => $bill,
            'amount' => 2000,
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'payment_date' => now()->toDateString(),
        ]);

        $this->assertEquals(2000, $payment->amount);
        $this->assertEquals($vendor->id, $payment->vendor_id);

        $this->assertDatabaseHas('bills', [
            'id' => $bill->id,
            'balance' => 0,
        ]);

        $this->assertDatabaseHas('vendor_payments', [
            'bill_id' => $bill->id,
            'vendor_id' => $vendor->id,
            'amount' => 2000,
        ]);
    }
}