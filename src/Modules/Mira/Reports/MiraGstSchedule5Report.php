<?php

namespace Hickr\Accounting\Modules\Mira\Reports;

use Hickr\Accounting\Models\JournalLine;
use Illuminate\Support\Carbon;

class MiraGstSchedule5Report
{
    public function generate(array $data): array
    {
        $tenantId = $data['tenant_id'];
        $from = Carbon::parse($data['date_from']);
        $to = Carbon::parse($data['date_to']);

        return JournalLine::query()
            ->with('journalEntry')
            ->where('tenant_id', $tenantId)
            ->whereHas('journalEntry', fn ($q) => $q->whereBetween('date', [$from, $to]))
            ->where(function ($q) {
                $q->whereHas('account', fn ($q) => $q->where('type', 'expense'))
                    ->whereNotNull('meta->supplier_name')
                    ->whereNotNull('meta->supplier_tin')
                    ->whereNotNull('meta->invoice_number')
                    ->whereNotNull('meta->gst_amount')
                    ->whereNotNull('meta->net_amount');
            })
            ->get()
            ->map(function ($line) {
                return [
                    'supplier_name'   => $line->meta['supplier_name'] ?? null,
                    'supplier_tin'    => $line->meta['supplier_tin'] ?? null,
                    'invoice_number'  => $line->meta['invoice_number'] ?? null,
                    'net_amount'      => (float) ($line->meta['net_amount'] ?? 0),
                    'gst_amount'      => (float) ($line->meta['gst_amount'] ?? 0),
                    'total_amount'    => (float) ($line->meta['net_amount'] ?? 0) + (float) ($line->meta['gst_amount'] ?? 0),
                ];
            })
            ->toArray();
    }
}