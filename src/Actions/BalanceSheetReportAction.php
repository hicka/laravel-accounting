<?php

namespace Hickr\Accounting\Actions;

use Hickr\Accounting\Models\JournalLine;
use Illuminate\Support\Carbon;

class BalanceSheetReportAction
{
    public static function run(array $data): array
    {
        $tenantId = $data['tenant_id'];
        $asOf = Carbon::parse($data['date_to']);
        $groupByAccount = $data['group_by_account'] ?? false;

        $lines = JournalLine::with('account')
            ->where('tenant_id', $tenantId)
            ->whereHas('journalEntry', fn ($q) => $q
                ->whereDate('date', '<=', $asOf)
            )
            ->whereHas('account', fn ($q) => $q->whereIn('type', ['asset', 'liability', 'equity']))
            ->get();

        $assets = $lines->filter(fn ($l) => $l->account->type === 'asset');
        $liabilities = $lines->filter(fn ($l) => $l->account->type === 'liability');
        $equity = $lines->filter(fn ($l) => $l->account->type === 'equity');

        $result = [];

        $summarize = function ($group, $type) use ($groupByAccount) {
            if ($groupByAccount) {
                return $group->groupBy('account_id')->map(function ($lines, $id) use ($type) {
                    $amount = $lines->reduce(function ($carry, $line) use ($type) {
                        $delta = match ($line->type) {
                            'debit' => $type === 'asset' ? 1 : -1,
                            'credit' => $type === 'asset' ? -1 : 1,
                        };
                        return $carry + ($line->base_currency_amount * $delta);
                    }, 0);

                    return [
                        'account_id' => $id,
                        'name' => $lines->first()->account->name,
                        'amount' => $amount,
                    ];
                })->values()->toArray();
            } else {
                return $group->reduce(function ($carry, $line) use ($type) {
                    $delta = match ($line->type) {
                        'debit' => $type === 'asset' ? 1 : -1,
                        'credit' => $type === 'asset' ? -1 : 1,
                    };
                    return $carry + ($line->base_currency_amount * $delta);
                }, 0);
            }
        };

        $result['assets'] = $summarize($assets, 'asset');
        $result['liabilities'] = $summarize($liabilities, 'liability');
        $result['equity'] = $summarize($equity, 'equity');

        $result['totals'] = [
            'assets' => $groupByAccount ? collect($result['assets'])->sum('amount') : $result['assets'],
            'liabilities' => $groupByAccount ? collect($result['liabilities'])->sum('amount') : $result['liabilities'],
            'equity' => $groupByAccount ? collect($result['equity'])->sum('amount') : $result['equity'],
        ];

        return $result;
    }
}