<?php

namespace Hickr\Accounting\Actions\Customers;

use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\CustomerCreditBalance;
use Hickr\Accounting\Models\Invoice;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\JournalLine;
use Hickr\Accounting\Models\Payment;
use Hickr\Accounting\Models\TenantConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ApplyCustomerCreditToInvoiceAction
{
    public static function execute(array $data): Payment
    {
        return DB::transaction(function () use ($data) {

            $tenantId = $data['tenant_id'];
            $invoice = $data['invoice'];
            $amount = $data['amount'];
            $currency = $data['currency_code'] ?? $invoice->currency_code;
            $rate = $data['exchange_rate'] ?? 1;
            $date = $data['date'] ?? now()->toDateString();

            $credit = CustomerCreditBalance::where('tenant_id', $tenantId)
                ->where('customer_id', $invoice->customer_id)
                ->firstOrFail();

            // Ensure enough credit exists
            if ($credit->base_currency_amount < $amount * $rate) {
                throw new \Exception("Insufficient credit balance.");
            }

            $config = TenantConfig::where('tenant_id', $tenantId)->firstOrFail();

            // 1. Record journal entry for applying credit
            $entry = JournalEntry::create([
                'tenant_id'            => $tenantId,
                'currency_code'        => $currency,
                'exchange_rate'        => $rate,
                'base_currency_amount' => $amount * $rate,
                'description'          => 'Credit applied to Invoice #' . $invoice->id,
                'date'                 => $date,
            ]);

            $entry->lines()->createMany([
                [
                    'tenant_id'            => $tenantId,
                    'account_id'           => $config->default_receivable_account_id,
                    'type'                 => 'credit',
                    'amount'               => $amount,
                    'base_currency_amount' => $amount * $rate,
                    'currency_code'        => $currency,
                ],
                [
                    'tenant_id'            => $tenantId,
                    'account_id'           => $config->default_credit_account_id,
                    'type'                 => 'debit',
                    'amount'               => $amount,
                    'base_currency_amount' => $amount * $rate,
                    'currency_code'        => $currency,
                ],
            ]);

            // 2. Update invoice
            $invoice->paid_amount += $amount;
            $invoice->balance -= $amount;
            $invoice->save();

            // 3. Reduce credit balance
            $credit->base_currency_amount -= $amount * $rate;
            $credit->save();

            // 4. Log the application
            return Payment::create([
                'tenant_id'     => $tenantId,
                'customer_id'   => $invoice->customer_id,
                'invoice_id'    => $invoice->id,
                'amount'        => $amount,
                'currency_code' => $currency,
                'exchange_rate' => $rate,
                'date'          => $date,
                'is_credit'     => true,
            ]);
        });
    }
}