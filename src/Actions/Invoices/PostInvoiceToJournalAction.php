<?php
namespace Hickr\Accounting\Actions\Invoices;

use Hickr\Accounting\Models\Invoice;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\JournalLine;
use Illuminate\Support\Facades\DB;

class PostInvoiceToJournalAction
{
    public static function execute(Invoice $invoice): JournalEntry
    {
        return DB::transaction(function () use ($invoice) {
            $entry = JournalEntry::create([
                'tenant_id' => $invoice->tenant_id,
                'date' => $invoice->date,
                'description' => 'Invoice #' . $invoice->id,
                'currency_code' => $invoice->currency_code,
                'exchange_rate' => $invoice->exchange_rate,
                'base_currency_amount' => $invoice->total * $invoice->exchange_rate,
            ]);

            // Debit Accounts Receivable (customer)
            $entry->lines()->create([
                'tenant_id' => $invoice->tenant_id,
                'account_id' => config('accounting.receivable_account_id'),
                'type' => 'debit',
                'amount' => $invoice->total,
                'base_currency_amount' => $invoice->total * $invoice->exchange_rate,
                'currency_code' => $invoice->currency_code,
            ]);

            // Credit individual income accounts
            foreach ($invoice->lines as $line) {
                $entry->lines()->create([
                    'tenant_id' => $invoice->tenant_id,
                    'account_id' => $line->account_id,
                    'type' => 'credit',
                    'amount' => $line->amount,
                    'base_currency_amount' => $line->amount * $invoice->exchange_rate,
                    'currency_code' => $invoice->currency_code,
                ]);
            }

            return $entry;
        });
    }
}