<?php

namespace Hickr\Accounting\Actions;

use Hickr\Accounting\Models\JournalTemplate;
use Hickr\Accounting\Actions\PostJournalEntryAction;

class PostJournalTemplateAction
{
    public static function execute(int $templateId): \Hickr\Accounting\Models\JournalEntry
    {
        $template = JournalTemplate::with('lines')->findOrFail($templateId);

        return PostJournalEntryAction::execute([
            'tenant_id' => $template->tenant_id,
            'date' => now()->toDateString(),
            'description' => $template->description,
            'currency_code' => $template->currency_code,
            'exchange_rate' => $template->exchange_rate,
            'inverse' => $template->inverse,
            'lines' => $template->lines->map(fn ($line) => [
                'account_id' => $line->account_id,
                'type' => $line->type,
                'amount' => $line->amount,
                'meta' => $line->meta,
            ])->toArray(),
        ]);
    }
}