<?php
namespace Hickr\Accounting\Actions;

use Illuminate\Support\Facades\DB;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\JournalLine;
use Hickr\Accounting\Support\Currency\MoneyFactory;
use Hickr\Accounting\Exceptions\UnbalancedJournalException;

class PostJournalEntryAction
{
    public static function execute(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            $tenant = $data['tenant'] ?? config('accounting.tenant_model')::find($data['tenant_id']);
            $baseCurrency = $tenant->getBaseCurrency();

            $entry = JournalEntry::create([
                'tenant_id'   => $tenant->id,
                'date'        => $data['date'],
                'description' => $data['description'] ?? null,
            ]);

            $totalDebit  = '0.000000';
            $totalCredit = '0.000000';

            foreach ($data['lines'] as $line) {
                $currencyCode = $line['currency_code'] ?? $baseCurrency;
                $exchangeRate = $line['exchange_rate'] ?? 1.0;
                $inverse = $line['inverse'] ?? false;

                $money = MoneyFactory::make($line['amount'], $currencyCode);

                $converted = $currencyCode !== $baseCurrency
                    ? MoneyFactory::convertToBase($money, $exchangeRate, $inverse)
                    : $money;

                $baseAmount = $converted->getAmount()->toScale(6); // Decimal string

                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'tenant_id'        => $tenant->id,
                    'account_id'       => $line['account_id'],
                    'amount'           => $baseAmount,
                    'currency_code'    => $currencyCode,
                    'exchange_rate'    => $exchangeRate,
                    'inverse'          => $inverse,
                    'side'             => $line['side'],
                    'memo'             => $line['memo'] ?? null,
                ]);

                if ($line['side'] === 'debit') {
                    $totalDebit = bcadd($totalDebit, $baseAmount, 6);
                } elseif ($line['side'] === 'credit') {
                    $totalCredit = bcadd($totalCredit, $baseAmount, 6);
                }
            }

            if (bccomp($totalDebit, $totalCredit, 6) !== 0) {
                throw new UnbalancedJournalException($totalDebit, $totalCredit);
            }

            return $entry->fresh(['lines']);
        });
    }
}