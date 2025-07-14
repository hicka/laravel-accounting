<?php

namespace Hickr\Accounting\Actions;

use Hickr\Accounting\Models\JournalLine;
use Illuminate\Support\Carbon;

class GeneralLedgerReportAction
{
    public static function run(array $data): array
    {
        $tenantId = $data['tenant_id'];
        $from = Carbon::parse($data['date_from']);
        $to = Carbon::parse($data['date_to']);

        $lines = JournalLine::with(['account', 'journalEntry'])
            ->where('tenant_id', $tenantId)
            ->whereHas('journalEntry', fn ($q) =>
            $q->whereBetween('date', [$from, $to])
            )
            ->orderBy('account_id')
            ->orderBy('journalEntry.date')
            ->orderBy('id')
            ->get();

        $grouped = $lines->groupBy('account_id');

        $report = [];

        foreach ($grouped as $accountId => $entries) {
            $account = $entries->first()->account;

            $entriesMapped = $entries->map(function ($line) {
                return [
                    'date' => $line->journalEntry->date->toDateString(),
                    'description' => $line->journalEntry->description,
                    'type' => $line->type,
                    'amount' => $line->base_currency_amount,
                    'entry_id' => $line->journal_entry_id,
                ];
            })->values();

            $report[] = [
                'account_id' => $accountId,
                'account_name' => $account->name,
                'account_type' => $account->type,
                'entries' => $entriesMapped,
                'total_debit' => $entries->where('type', 'debit')->sum('base_currency_amount'),
                'total_credit' => $entries->where('type', 'credit')->sum('base_currency_amount'),
            ];
        }

        return $report;
    }
}