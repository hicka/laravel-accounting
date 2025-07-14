<?php

namespace Hickr\Accounting\Actions;

use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\JournalLine;
use Illuminate\Support\Carbon;

class ProfitAndLossReportAction
{
    public static function run(array $data): array
    {
        $tenantId = $data['tenant_id'];
        $from = Carbon::parse($data['date_from']);
        $to = Carbon::parse($data['date_to']);
        $groupByAccount = $data['group_by_account'] ?? false;

        $lines = JournalLine::with('account')
            ->where('tenant_id', $tenantId)
            ->whereHas('journalEntry', fn ($q) => $q
                ->whereDate('date', '>=', $from)
                ->whereDate('date', '<=', $to)
            )
            ->whereHas('account', fn ($q) => $q->whereIn('type', ['revenue', 'expense']))
            ->get();

        $revenueLines = $lines->filter(fn ($line) => $line->account->type === 'revenue');
        $expenseLines = $lines->filter(fn ($line) => $line->account->type === 'expense');

        $result = [];

        // Group by account or type
        if ($groupByAccount) {
            $result['revenue'] = $revenueLines->groupBy('account_id')->map(function ($lines, $id) {
                return [
                    'account_id' => $id,
                    'name' => $lines->first()->account->name,
                    'amount' => $lines->reduce(fn ($carry, $line) =>
                        $carry + ($line->type === 'credit' ? $line->base_currency_amount : -$line->base_currency_amount), 0),
                ];
            })->values()->toArray();

            $result['expenses'] = $expenseLines->groupBy('account_id')->map(function ($lines, $id) {
                return [
                    'account_id' => $id,
                    'name' => $lines->first()->account->name,
                    'amount' => $lines->reduce(fn ($carry, $line) =>
                        $carry + ($line->type === 'debit' ? $line->base_currency_amount : -$line->base_currency_amount), 0),
                ];
            })->values()->toArray();
        } else {
            $result['revenue'] = $revenueLines->sum(fn ($line) =>
            $line->type === 'credit' ? $line->base_currency_amount : -$line->base_currency_amount
            );

            $result['expenses'] = $expenseLines->sum(fn ($line) =>
            $line->type === 'debit' ? $line->base_currency_amount : -$line->base_currency_amount
            );
        }

        // Net profit = total revenue - total expense
        $totalRevenue = $groupByAccount
            ? collect($result['revenue'])->sum('amount')
            : $result['revenue'];

        $totalExpenses = $groupByAccount
            ? collect($result['expenses'])->sum('amount')
            : $result['expenses'];

        $result['net_profit'] = $totalRevenue - $totalExpenses;

        return $result;
    }
}