<?php

namespace Hickr\Accounting\Actions\Vendors;

use Hickr\Accounting\Models\Bill;
use Hickr\Accounting\Models\BillLine;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\JournalLine;
use Hickr\Accounting\Models\TenantConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CreateBillAction
{
    public static function execute(array $data): Bill
    {
        return DB::transaction(function () use ($data) {
            $tenantId = $data['tenant_id'];
            $vendorId = $data['vendor_id'];
            $date = $data['date'] ?? now()->toDateString();
            $currency = $data['currency_code'] ?? 'MVR';
            $rate = $data['exchange_rate'] ?? 1;

            $config = TenantConfig::where('tenant_id', $tenantId)->firstOrFail();

            // 1. Create the Bill
            $bill = Bill::create([
                'tenant_id' => $tenantId,
                'vendor_id' => $vendorId,
                'date' => $date,
                'due_date' => $data['due_date'] ?? now()->addDays(30),
                'currency_code' => $currency,
                'exchange_rate' => $rate,
                'total' => $data['total'],
                'balance' => $data['total'],
                'notes' => $data['notes'] ?? null,
            ]);

            // 2. Add Bill Lines
            foreach ($data['lines'] as $line) {
                $bill->lines()->create([
                    'tenant_id' => $tenantId,
                    'account_id' => $line['account_id'],
                    'amount' => $line['amount'],
                    'description' => $line['description'] ?? null,
                ]);
            }

            // 3. Create Journal Entry
            $entry = JournalEntry::create([
                'tenant_id' => $tenantId,
                'currency_code' => $currency,
                'exchange_rate' => $rate,
                'base_currency_amount' => $data['total'] * $rate,
                'description' => 'Vendor Bill #' . $bill->id,
                'date' => $date,
            ]);

            // 4. Journal Lines
            $lines = [];

            // Debit expense or other accounts
            foreach ($bill->lines as $billLine) {
                $lines[] = new JournalLine([
                    'tenant_id' => $tenantId,
                    'account_id' => $billLine->account_id,
                    'type' => 'debit',
                    'amount' => $billLine->amount,
                    'base_currency_amount' => $billLine->amount * $rate,
                    'currency_code' => $currency,
                ]);
            }

            // Credit accounts payable
            $lines[] = new JournalLine([
                'tenant_id' => $tenantId,
                'account_id' => $config->default_payable_account_id,
                'type' => 'credit',
                'amount' => $bill->total,
                'base_currency_amount' => $bill->total * $rate,
                'currency_code' => $currency,
            ]);

            $entry->lines()->saveMany($lines);

            return $bill;
        });
    }
}