<?php

namespace Hickr\Accounting\Actions\Customers;

use Hickr\Accounting\Models\CustomerCreditBalance;
use Hickr\Accounting\Models\CustomerCreditRefund;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\TenantConfig;
use Illuminate\Support\Facades\DB;

class RefundCustomerCreditAction
{
    public static function execute(array $data): CustomerCreditRefund
    {
        return DB::transaction(function () use ($data) {
            $credit = CustomerCreditBalance::findOrFail($data['credit_balance_id']);

            if ($credit->amount < $data['amount']) {
                throw new \Exception("Insufficient credit balance.");
            }

            $refund = CustomerCreditRefund::create([
                'tenant_id'            => $credit->tenant_id,
                'customer_id'          => $credit->customer_id,
                'credit_balance_id'    => $credit->id,
                'amount'               => $data['amount'],
                'currency_code'        => $credit->currency_code,
                'exchange_rate'        => $credit->exchange_rate,
                'base_currency_amount' => $credit->exchange_rate * $data['amount'],
                'refund_method'        => $data['refund_method'] ?? 'cash',
                'date'                 => $data['date'] ?? now()->toDateString(),
            ]);

            // Create journal entry
            $journal = JournalEntry::create([
                'tenant_id'            => $credit->tenant_id,
                'currency_code'        => $credit->currency_code,
                'exchange_rate'        => $credit->exchange_rate,
                'base_currency_amount' => $refund->base_currency_amount,
                'description'          => 'Refund of customer credit',
                'date'                 => $refund->date,
            ]);

            $cashOrBankAccount = match ($refund->refund_method) {
                'bank' => config('accounting.default_bank_account_id'),
                default => config('accounting.default_cash_account_id'),
            };

            $receivable = TenantConfig::where('tenant_id', $credit->tenant_id)->firstOrFail()->default_receivable_account_id;

            $journal->lines()->createMany([
                [
                    'tenant_id'            => $credit->tenant_id,
                    'account_id'           => $receivable,
                    'type'                 => 'debit',
                    'amount'               => $refund->amount,
                    'base_currency_amount' => $refund->base_currency_amount,
                    'currency_code'        => $refund->currency_code,
                ],
                [
                    'tenant_id'            => $credit->tenant_id,
                    'account_id'           => $cashOrBankAccount,
                    'type'                 => 'credit',
                    'amount'               => $refund->amount,
                    'base_currency_amount' => $refund->base_currency_amount,
                    'currency_code'        => $refund->currency_code,
                ],
            ]);

            $refund->journal_entry_id = $journal->id;
            $refund->save();

            // Reduce credit balance
            $credit->amount -= $refund->amount;
            $credit->base_currency_amount -= $refund->base_currency_amount;
            $credit->save();

            return $refund;
        });
    }
}