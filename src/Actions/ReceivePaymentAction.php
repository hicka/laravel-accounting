<?php

namespace Hickr\Accounting\Actions;

use Hickr\Accounting\Models\Payment;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\JournalLine;
use Hickr\Accounting\Models\TenantConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ReceivePaymentAction
{
    public static function execute(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $tenantId = $data['tenant_id'];
            $invoice = $data['invoice'];
            $amount = $data['amount'];
            $currency = $data['currency_code'] ?? 'MVR';
            $rate = $data['exchange_rate'] ?? 1;
            $date = $data['date'] ?? now()->toDateString();

            $config = TenantConfig::where('tenant_id', $tenantId)->firstOrFail();

            // 1. Create Payment
            $payment = Payment::create([
                'tenant_id'     => $tenantId,
                'invoice_id'    => $invoice->id,
                'amount'        => $amount,
                'currency_code' => $currency,
                'exchange_rate' => $rate,
                'date'          => $date,
            ]);

            // 2. Create Journal Entry
            $entry = JournalEntry::create([
                'tenant_id'            => $tenantId,
                'currency_code'        => $currency,
                'exchange_rate'        => $rate,
                'base_currency_amount' => $rate * $amount,
                'description'          => 'Payment received for Invoice #' . $invoice->id,
                'date'                 => $date,
            ]);

            // 3. Journal Lines
            $entry->lines()->createMany([
                [
                    'tenant_id'            => $tenantId,
                    'account_id'           => $config->default_cash_account_id,
                    'type'                 => 'debit',
                    'amount'               => $amount,
                    'base_currency_amount' => $rate * $amount,
                    'currency_code'        => $currency,
                ],
                [
                    'tenant_id'            => $tenantId,
                    'account_id'           => $config->default_receivable_account_id,
                    'type'                 => 'credit',
                    'amount'               => $amount,
                    'base_currency_amount' => $rate * $amount,
                    'currency_code'        => $currency,
                ],
            ]);

            // 4. Update Invoice (assumes paid column or similar logic)
            $invoice->paid_amount += $amount;
            $invoice->save();

            return $payment;
        });
    }
}