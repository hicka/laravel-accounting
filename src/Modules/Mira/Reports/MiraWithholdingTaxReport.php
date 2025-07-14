<?php

namespace Hickr\Accounting\Modules\Mira\Reports;

use Hickr\Accounting\Models\JournalLine;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class MiraWithholdingTaxReport
{
    public function generate(array $data): array
    {
        $tenantId = $data['tenant_id'];
        $from = Carbon::parse($data['date_from']);
        $to = Carbon::parse($data['date_to']);

        $lines = JournalLine::query()
            ->with(['account', 'journalEntry'])
            ->where('tenant_id', $tenantId)
            ->whereHas('journalEntry', fn($q) => $q->whereBetween('date', [$from, $to]))
            ->whereHas('account', fn($q) => $q->whereNotNull('tax_type')->where('tax_type', 'like', 'wht_%'))
            ->get();

        $report = [];

        foreach ($lines as $line) {
            $type = $line->account->tax_type;
            $rate = Config::get("accounting.modules.mira.wht_rates.$type", 0);

            $amount = $line->base_currency_amount;
            $withheld = $amount * $rate;

            $report[] = [
                'date' => $line->journalEntry->date,
                'account_name' => $line->account->name,
                'tax_type' => $type,
                'rate' => $rate,
                'amount' => $amount,
                'withheld_amount' => round($withheld, 2),
            ];
        }

        return [
            'lines' => $report,
            'total_withheld' => collect($report)->sum('withheld_amount'),
        ];
    }
}