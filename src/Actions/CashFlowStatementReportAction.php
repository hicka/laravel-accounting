<?php

namespace Hickr\Accounting\Actions;

use Hickr\Accounting\Models\JournalLine;
use Illuminate\Support\Carbon;

class CashFlowStatementReportAction
{
    public static function run(array $data): array
    {
        $tenantId = $data['tenant_id'];
        $from = Carbon::parse($data['date_from']);
        $to = Carbon::parse($data['date_to']);

        $lines = JournalLine::query()
            ->with('account')
            ->where('tenant_id', $tenantId)
            ->whereHas('journalEntry', function ($query) use ($from, $to) {
                $query->whereBetween('date', [$from, $to]);
            })
            ->get();

        $map = config('accounting.cash_flow_map', [
            'operating' => ['revenue', 'expense'],
            'investing' => ['asset'],
            'financing' => ['equity', 'liability'],
        ]);

        $result = [
            'operating' => 0,
            'investing' => 0,
            'financing' => 0,
        ];

        foreach ($lines as $line) {
            $type = strtolower($line->account->type);
            $amount = $line->base_currency_amount;

            if (in_array($type, ['revenue', 'liability', 'equity'])) {
                $amount *= $line->type === 'credit' ? 1 : -1;
            } else {
                $amount *= $line->type === 'debit' ? -1 : 1;
            }

            foreach ($map as $section => $accountTypes) {
                if (in_array($type, $accountTypes)) {
                    $result[$section] += $amount;
                    break;
                }
            }
        }

        return [
            'cash_flows' => $result,
            'net_cash_flow' => array_sum($result),
        ];
    }
}