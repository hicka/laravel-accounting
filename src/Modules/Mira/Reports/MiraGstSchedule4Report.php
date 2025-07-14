<?php

namespace Hickr\Accounting\Modules\Mira\Reports;

use Hickr\Accounting\Models\JournalLine;
use Illuminate\Support\Carbon;

class MiraGstSchedule4Report
{
    public function generate(array $data): array
    {
        $tenantId = $data['tenant_id'];
        $from = Carbon::parse($data['date_from']);
        $to = Carbon::parse($data['date_to']);

        return JournalLine::query()
            ->with(['account', 'journalEntry'])
            ->where('tenant_id', $tenantId)
            ->whereHas('journalEntry', fn ($q) => $q->whereBetween('date', [$from, $to]))
            ->where(function ($q) {
                $q->whereHas('account', fn ($q) => $q->where('type', 'revenue'))
                    ->whereIn('meta->gst_type', ['zero_rated', 'exempt']);
            })
            ->get()
            ->map(function ($line) {
                return [
                    'description' => $line->journalEntry->description ?? null,
                    'income_type' => $line->meta['gst_type'] ?? null,
                    'amount' => $line->amount,
                ];
            })
            ->toArray();
    }
}