<?php

namespace Hickr\Accounting\Actions\Vendors;

use Hickr\Accounting\Models\VendorPayment;
use Hickr\Accounting\Models\Bill;
use Hickr\Accounting\Models\TenantConfig;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\JournalLine;
use Illuminate\Support\Facades\DB;

class PayBillAction
{
    public static function execute(array $data): VendorPayment
    {
        return DB::transaction(function () use ($data) {
            $bill = $data['bill'];
            $tenantId = $data['tenant_id'];
            $amount = $data['amount'];
            $currency = $data['currency_code'] ?? $bill->currency_code;
            $rate = $data['exchange_rate'] ?? $bill->exchange_rate;
            $date = $data['date'] ?? now()->toDateString();

            $config = TenantConfig::where('tenant_id', $tenantId)->firstOrFail();

            if ($amount > $bill->balance) {
                throw new \Exception('Payment exceeds bill balance.');
            }

            // 1. Record Vendor Payment
            $payment = VendorPayment::create([
                'tenant_id'     => $tenantId,
                'vendor_id'     => $bill->vendor_id,
                'bill_id'       => $bill->id,
                'amount'        => $amount,
                'currency_code' => $currency,
                'exchange_rate' => $rate,
                'date'          => $date,
                'notes'         => $data['notes'] ?? null,
            ]);

            // 2. Create Journal Entry
            $entry = JournalEntry::create([
                'tenant_id'            => $tenantId,
                'currency_code'        => $currency,
                'exchange_rate'        => $rate,
                'base_currency_amount' => $amount * $rate,
                'description'          => 'Bill Payment for Bill #' . $bill->id,
                'date'                 => $date,
            ]);

            // 3. Journal Lines
            $entry->lines()->createMany([
                [
                    'tenant_id'            => $tenantId,
                    'account_id'           => $config->default_payable_account_id,
                    'type'                 => 'debit',
                    'amount'               => $amount,
                    'base_currency_amount' => $amount * $rate,
                    'currency_code'        => $currency,
                ],
                [
                    'tenant_id'            => $tenantId,
                    'account_id'           => $config->default_cash_account_id,
                    'type'                 => 'credit',
                    'amount'               => $amount,
                    'base_currency_amount' => $amount * $rate,
                    'currency_code'        => $currency,
                ],
            ]);

            // 4. Update Bill Balance
            $bill->paid_amount += $amount;
            $bill->balance -= $amount;
            $bill->save();

            return $payment;
        });
    }
}