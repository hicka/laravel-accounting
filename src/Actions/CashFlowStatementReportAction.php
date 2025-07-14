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

        $lines = JournalLine::with('account')
            ->where('tenant_id', $tenantId)
            ->whereHas('journalEntry', fn ($q) =>
            $q->whereBetween('date', [$from, $to])
            )
            ->get();

        $map = config('accounting.cash_flow_map');

        $result = [
            'operating' => 0,
            'investing' => 0,
            'financing' => 0,
        ];

        foreach ($lines as $line) {
            $type = $line->account->type;
            $amount = $line->type === 'debit' ? $line->base_currency_amount : -$line->base_currency_amount;

            foreach ($map as $section => $accountTypes) {
                if (in_array($type, $accountTypes)) {
                    $result[$section] += $amount;
                    break;
                }
            }
        }

        return [
            'cash_flows' => [
                'operating' => round($result['operating'], 2),
                'investing' => round($result['investing'], 2),
                'financing' => round($result['financing'], 2),
            ],
            'net_cash_flow' => round(array_sum($result), 2),
        ];
    }
}