<?php

namespace Hickr\Accounting\Actions\Invoices;

use Hickr\Accounting\Models\Invoice;
use Hickr\Accounting\Models\InvoiceLine;
use Illuminate\Support\Facades\DB;

class CreateInvoiceAction
{
    public static function execute(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $invoice = Invoice::create([
                'tenant_id' => $data['tenant_id'],
                'customer_id' => $data['customer_id'],
                'date' => $data['date'] ?? now()->toDateString(),
                'due_date' => $data['due_date'] ?? now()->addDays(30)->toDateString(),
                'currency_code' => $data['currency_code'] ?? 'MVR',
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'description' => $data['description'] ?? null,
                'total' => 0, // temp
            ]);

            $total = 0;

            foreach ($data['lines'] as $line) {
                $amount = (float) $line['amount'];
                $invoice->lines()->create([
                    'tenant_id' => $invoice->tenant_id,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'amount' => $amount,
                ]);
                $total += $amount;
            }

            $invoice->update(['total' => $total]);

            return $invoice;
        });
    }
}