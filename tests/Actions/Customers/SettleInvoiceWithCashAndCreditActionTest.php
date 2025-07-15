<?php

namespace Hickr\Accounting\Tests\Actions\Customers;

use Hickr\Accounting\Actions\Customers\SettleInvoiceWithCashAndCreditAction;
use Hickr\Accounting\Models\TenantConfig;
use Hickr\Accounting\Models\CustomerCreditBalance;
use Hickr\Accounting\Models\Invoice;
use Hickr\Accounting\Models\Payment;
use Hickr\Accounting\Tests\TestCase;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\Customer;
use Hickr\Accounting\Models\Tenant;

class SettleInvoiceWithCashAndCreditActionTest extends TestCase
{
    public function test_it_settles_invoice_with_cash_and_credit()
    {
        $tenant = Tenant::factory()->create();

        $receivable = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id]);
        $cash = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id]);
        $credit = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id]);

        TenantConfig::create([
            'tenant_id' => $tenant->id,
            'default_receivable_account_id' => $receivable->id,
            'default_cash_account_id' => $cash->id,
            'default_credit_account_id' => $credit->id,
        ]);

        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        CustomerCreditBalance::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'base_currency_amount' => 1000,
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'total' => 1500,
            'balance' => 1500,
            'paid_amount' => 0,
            'currency_code' => 'MVR',
        ]);

        $payment = SettleInvoiceWithCashAndCreditAction::execute([
            'tenant_id' => $tenant->id,
            'invoice' => $invoice,
            'cash_amount' => 500,
            'credit_amount' => 1000,
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'date' => now()->toDateString(),
        ]);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals(1500, $payment->amount);
        $this->assertEquals($invoice->id, $payment->invoice_id);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'balance' => 0,
            'paid_amount' => 1500,
        ]);

        $this->assertDatabaseHas('customer_credit_balances', [
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'base_currency_amount' => 0,
        ]);
    }
}