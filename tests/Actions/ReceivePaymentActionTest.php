<?php

namespace Hickr\Accounting\Tests\Actions;

use Hickr\Accounting\Actions\ReceivePaymentAction;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\Customer;
use Hickr\Accounting\Models\CustomerCreditBalance;
use Hickr\Accounting\Models\Invoice;
use Hickr\Accounting\Models\Tenant;
use Hickr\Accounting\Models\TenantConfig;
use Hickr\Accounting\Tests\TestCase;

class ReceivePaymentActionTest extends TestCase
{
    public function test_it_receives_full_payment_for_invoice()
    {
        $tenant = Tenant::factory()->create();

        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $receivable = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id]);
        $cash = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id]);

        TenantConfig::create([
            'tenant_id' => $tenant->id,
            'default_receivable_account_id' => $receivable->id,
            'default_cash_account_id' => $cash->id,
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'total' => 1000.00,
            'balance' => 1000.00,
            'paid_amount' => 0,
        ]);

        $this->assertEquals(1000.00, $invoice->fresh()->balance);

        $payment = ReceivePaymentAction::execute([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'amount' => 1000.00,
            'currency_code' => 'MVR',
            'payment_date' => now()->toDateString(),
            'invoice' => $invoice,
        ]);

        $invoice = $invoice->fresh();

        $this->assertEquals(0.00, $invoice->fresh()->balance);
        $this->assertEquals(1000.00, $payment->amount);
        $this->assertEquals($customer->id, $payment->customer_id);

        $this->assertEquals(0.00, $invoice->fresh()->balance);

    }

    public function test_it_saves_customer_credit_balance()
    {
        $credit = CustomerCreditBalance::factory()->create([
            'amount' => 500,
        ]);

        $this->assertDatabaseHas('customer_credit_balances', [
            'id' => $credit->id,
            'amount' => 500,
        ]);
    }
}