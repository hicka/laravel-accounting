<?php

namespace Hickr\Accounting\Actions;

use Hickr\Accounting\Exceptions\UnbalancedJournalException;
use Illuminate\Support\Facades\DB;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\JournalLine;

class PostJournalEntryAction
{
    public static function execute(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            $entry = JournalEntry::create([
                'tenant_id'   => $data['tenant_id'],
                'date'        => $data['date'],
                'description' => $data['description'] ?? null,
            ]);

            $totalDebit  = '0.000000';
            $totalCredit = '0.000000';

            foreach ($data['lines'] as $line) {
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'tenant_id'        => $data['tenant_id'],
                    'account_id'       => $line['account_id'],
                    'amount'           => $line['amount'],
                    'side'             => $line['side'],
                    'memo'             => $line['memo'] ?? null,
                ]);

                if ($line['side'] === 'debit') {
                    $totalDebit = bcadd($totalDebit, $line['amount'], 6);
                } elseif ($line['side'] === 'credit') {
                    $totalCredit = bcadd($totalCredit, $line['amount'], 6);
                }
            }

            if (bccomp($totalDebit, $totalCredit, 6) !== 0) {
                throw new UnbalancedJournalException($totalDebit, $totalCredit);
            }

            return $entry->fresh(['lines']);
        });
    }
}