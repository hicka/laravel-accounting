<?php

namespace Hickr\Accounting\Tests\Actions\Customers;

use Hickr\Accounting\Actions\Customers\ApplyCustomerCreditToInvoiceAction;
use Hickr\Accounting\Models\Customer;
use Hickr\Accounting\Models\CustomerCreditBalance;
use Hickr\Accounting\Models\Invoice;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\Payment;
use Hickr\Accounting\Models\Tenant;
use Hickr\Accounting\Models\TenantConfig;
use Hickr\Accounting\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApplyCustomerCreditToInvoiceActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_applies_credit_to_invoice()
    {
        $tenant = Tenant::factory()->create();
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $receivable = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id]);
        $credit = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id]);

        TenantConfig::create([
            'tenant_id' => $tenant->id,
            'default_receivable_account_id' => $receivable->id,
            'default_credit_account_id' => $credit->id,
        ]);

        CustomerCreditBalance::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'base_currency_amount' => 1000,
        ]);

        $invoice = Invoice::factory()->create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'total' => 1000,
            'balance' => 1000,
            'paid_amount' => 0,
        ]);

        $payment = ApplyCustomerCreditToInvoiceAction::execute([
            'tenant_id' => $tenant->id,
            'invoice' => $invoice,
            'amount' => 1000,
            'currency_code' => 'MVR',
        ]);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertTrue($payment->is_credit);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'balance' => 0,
            'paid_amount' => 1000,
        ]);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 1000,
            'is_credit' => true,
        ]);

        $this->assertDatabaseHas('customer_credit_balances', [
            'customer_id' => $customer->id,
            'tenant_id' => $tenant->id,
            'base_currency_amount' => 0,
        ]);
    }
}