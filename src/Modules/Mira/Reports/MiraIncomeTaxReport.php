<?php

namespace Hickr\Accounting\Modules\Mira\Reports;

use Hickr\Accounting\Models\JournalLine;
use Illuminate\Support\Carbon;

class MiraIncomeTaxReport
{
    public function generate(array $data): array
    {
        $tenantId = $data['tenant_id'];
        $from = Carbon::parse($data['date_from']);
        $to = Carbon::parse($data['date_to']);

        $lines = JournalLine::query()
            ->with('account')
            ->where('tenant_id', $tenantId)
            ->whereHas('journalEntry', fn($q) => $q->whereBetween('date', [$from, $to]))
            ->get();

        $totals = [
            'revenue' => 0,
            'direct_expense' => 0,
            'operating_expense' => 0,
            'non_taxable_income' => 0,
            'adjustments' => 0,
        ];

        foreach ($lines as $line) {
            $amount = $line->base_currency_amount;
            $type = $line->account->type;

            match ($type) {
                'revenue' => $totals['revenue'] += ($line->type === 'credit' ? $amount : -$amount),
                'expense' => $totals['operating_expense'] += ($line->type === 'debit' ? $amount : -$amount),
                'cost_of_sales' => $totals['direct_expense'] += ($line->type === 'debit' ? $amount : -$amount),
                'non_taxable' => $totals['non_taxable_income'] += $amount,
                default => null,
            };
        }

        $gross_profit = $totals['revenue'] - $totals['direct_expense'];
        $net_profit = $gross_profit - $totals['operating_expense'];
        $taxable_profit = $net_profit - $totals['non_taxable_income'] + $totals['adjustments'];

        return [
            'revenue' => $totals['revenue'],
            'direct_expense' => $totals['direct_expense'],
            'operating_expense' => $totals['operating_expense'],
            'non_taxable_income' => $totals['non_taxable_income'],
            'adjustments' => $totals['adjustments'],
            'gross_profit' => $gross_profit,
            'net_profit' => $net_profit,
            'taxable_profit' => $taxable_profit,
        ];
    }
}