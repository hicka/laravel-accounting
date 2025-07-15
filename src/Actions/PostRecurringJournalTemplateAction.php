<?php

namespace Hickr\Accounting\Actions;

use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\JournalLine;
use Hickr\Accounting\Models\JournalTemplate;
use Illuminate\Support\Facades\DB;

class PostRecurringJournalTemplateAction
{
    public static function execute(JournalTemplate $template): JournalEntry
    {
        return DB::transaction(function () use ($template) {
            $entry = JournalEntry::create([
                'tenant_id' => $template->tenant_id,
                'date' => now()->toDateString(),
                'description' => $template->description ?? $template->name,
                'currency_code' => $template->currency_code,
                'exchange_rate' => $template->exchange_rate,
                'base_currency_amount' => 0,
            ]);

            $lines = $template->lines->map(function ($line) use ($template) {
                return new JournalLine([
                    'tenant_id' => $template->tenant_id,
                    'account_id' => $line->account_id,
                    'type' => $line->type,
                    'amount' => $line->amount,
                    'base_currency_amount' => $line->base_currency_amount ?? $line->amount,
                    'currency_code' => $line->currency_code ?? $template->currency_code,
                ]);
            });

            $entry->lines()->saveMany($lines);

            $template->last_posted_at = now();
            $template->save();

            return $entry;
        });
    }
}