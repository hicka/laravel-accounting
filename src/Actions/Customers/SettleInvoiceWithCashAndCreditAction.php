<?php

namespace Hickr\Accounting\Actions\Customers;

use Hickr\Accounting\Models\Invoice;
use Hickr\Accounting\Models\TenantConfig;
use Hickr\Accounting\Models\CustomerCreditBalance;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\JournalLine;
use Hickr\Accounting\Models\Payment;
use Illuminate\Support\Facades\DB;

class SettleInvoiceWithCashAndCreditAction
{
    public static function execute(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $tenantId = $data['tenant_id'];
            $invoice = $data['invoice'];
            $cashAmount = $data['cash_amount'] ?? 0;
            $creditAmount = $data['credit_amount'] ?? 0;
            $currency = $data['currency_code'] ?? $invoice->currency_code;
            $rate = $data['exchange_rate'] ?? 1;
            $date = $data['date'] ?? now()->toDateString();

            $config = TenantConfig::where('tenant_id', $tenantId)->firstOrFail();

            $totalAmount = $cashAmount + $creditAmount;
            $baseTotal = $totalAmount * $rate;

            // Journal Entry
            $entry = JournalEntry::create([
                'tenant_id' => $tenantId,
                'currency_code' => $currency,
                'exchange_rate' => $rate,
                'base_currency_amount' => $baseTotal,
                'description' => "Mixed settlement for Invoice #{$invoice->id}",
                'date' => $date,
            ]);

            $lines = [];

            if ($cashAmount > 0) {
                $lines[] = new JournalLine([
                    'tenant_id' => $tenantId,
                    'account_id' => $config->default_cash_account_id,
                    'type' => 'debit',
                    'amount' => $cashAmount,
                    'base_currency_amount' => $cashAmount * $rate,
                    'currency_code' => $currency,
                ]);
            }

            if ($creditAmount > 0) {
                $lines[] = new JournalLine([
                    'tenant_id' => $tenantId,
                    'account_id' => $config->default_credit_account_id,
                    'type' => 'debit',
                    'amount' => $creditAmount,
                    'base_currency_amount' => $creditAmount * $rate,
                    'currency_code' => $currency,
                ]);

                // Reduce customer credit
                $credit = CustomerCreditBalance::where('tenant_id', $tenantId)
                    ->where('customer_id', $invoice->customer_id)
                    ->firstOrFail();

                if ($credit->base_currency_amount < $creditAmount * $rate) {
                    throw new \Exception("Insufficient credit balance.");
                }

                $credit->base_currency_amount -= $creditAmount * $rate;
                $credit->save();
            }

            // Credit the receivable
            $lines[] = new JournalLine([
                'tenant_id' => $tenantId,
                'account_id' => $config->default_receivable_account_id,
                'type' => 'credit',
                'amount' => $totalAmount,
                'base_currency_amount' => $baseTotal,
                'currency_code' => $currency,
            ]);

            $entry->lines()->saveMany($lines);

            // Update invoice
            $invoice->paid_amount += $totalAmount;
            $invoice->balance -= $totalAmount;
            $invoice->save();

            // Record payment
            return Payment::create([
                'tenant_id' => $tenantId,
                'customer_id' => $invoice->customer_id,
                'invoice_id' => $invoice->id,
                'amount' => $totalAmount,
                'currency_code' => $currency,
                'exchange_rate' => $rate,
                'date' => $date,
                'is_credit' => $creditAmount > 0,
            ]);
        });
    }
}