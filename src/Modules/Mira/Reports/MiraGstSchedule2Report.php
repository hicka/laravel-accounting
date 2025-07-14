<?php
namespace Hickr\Accounting\Modules\Mira\Reports;

use Hickr\Accounting\Models\JournalLine;
use Illuminate\Support\Carbon;

class MiraGstSchedule2Report
{
    public function generate(array $data): array
    {
        $tenantId = $data['tenant_id'];
        $from = Carbon::parse($data['date_from']);
        $to = Carbon::parse($data['date_to']);

        return JournalLine::query()
            ->with(['account', 'journalEntry'])
            ->where('tenant_id', $tenantId)
            ->whereHas('journalEntry', fn($q) => $q->whereBetween('date', [$from, $to]))
            ->whereHas('account', fn($q) => $q->where('type', 'gst_input'))
            ->get()
            ->map(function ($line) {
                return [
                    'supplier_name' => $line->meta['supplier_name'] ?? null,
                    'invoice_number' => $line->meta['invoice_number'] ?? null,
                    'invoice_date' => optional($line->journalEntry->date)->toDateString(),
                    'gst_amount' => $line->amount,
                    'net_amount' => $line->meta['net_amount'] ?? null,
                    'total_amount' => $line->meta['total_amount'] ?? null,
                ];
            })->toArray();
    }
}