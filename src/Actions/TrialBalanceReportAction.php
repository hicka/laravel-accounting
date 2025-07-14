<?php

namespace Hickr\Accounting\Actions;

use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\JournalLine;
use Illuminate\Support\Facades\DB;

class TrialBalanceReportAction
{
    public static function run(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $fromDate = $params['date_from'] ?? null;
        $toDate = $params['date_to'] ?? null;
        $group = $params['group_by_type'] ?? false;

        $lines = JournalLine::query()
            ->select([
                'account_id',
                DB::raw("SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as debit"),
                DB::raw("SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as credit")
            ])
            ->whereHas('journalEntry', function ($q) use ($tenantId, $fromDate, $toDate) {
                $q->where('tenant_id', $tenantId);

                if ($fromDate) {
                    $q->where('date', '>=', $fromDate);
                }

                if ($toDate) {
                    $q->where('date', '<=', $toDate);
                }
            })
            ->groupBy('account_id')
            ->get()
            ->map(function ($line) {
                $account = ChartOfAccount::find($line->account_id);

                return [
                    'account_id' => $line->account_id,
                    'code'       => $account->code,
                    'name'       => $account->name,
                    'type'       => $account->type,
                    'debit'      => (float) $line->debit,
                    'credit'     => (float) $line->credit,
                    'balance'    => (float) $line->debit - (float) $line->credit,
                ];
            });

        if ($group) {
            return $lines->groupBy('type')->toArray();
        }

        return $lines->toArray();
    }
}
