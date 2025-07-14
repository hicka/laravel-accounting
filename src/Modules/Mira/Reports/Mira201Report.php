<?php

namespace Hickr\Accounting\Modules\Mira\Reports;

use Hickr\Accounting\Models\JournalLine;
use Illuminate\Support\Carbon;

class Mira201Report
{
    public function generate(array $data): array
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

        $totals = [
            'taxable_sales' => 0,
            'zero_rated_sales' => 0,
            'exempt_income' => 0,
            'output_tax' => 0,
            'input_tax' => 0,
        ];

        foreach ($lines as $line) {
            $taxType = $line->account->tax_type;
            $amount = $line->base_currency_amount;

            switch ($taxType) {
                case 'standard_gst':
                    if ($line->type === 'credit') {
                        $totals['taxable_sales'] += $amount;
                        $totals['output_tax'] += $amount * 0.06; // 6% default rate
                    }
                    break;

                case 'zero_gst':
                    if ($line->type === 'credit') {
                        $totals['zero_rated_sales'] += $amount;
                    }
                    break;

                case 'exempt':
                    if ($line->type === 'credit') {
                        $totals['exempt_income'] += $amount;
                    }
                    break;

                case 'input_tax':
                    if ($line->type === 'debit') {
                        $totals['input_tax'] += $amount * 0.06; // 6% claimable
                    }
                    break;
            }
        }

        return [
            'totals' => $totals,
            'net_gst_payable' => $totals['output_tax'] - $totals['input_tax'],
        ];
    }
}