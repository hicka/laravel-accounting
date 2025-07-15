<?php

namespace Hickr\Accounting\Actions;

use Hickr\Accounting\Models\JournalTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class GetDueRecurringJournalTemplatesAction
{
    public static function run(): Collection
    {
        $today = Carbon::today();

        return JournalTemplate::query()
            ->where('is_recurring', true)
            ->where('auto_post', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('last_posted_at')
                    ->orWhereRaw("DATE(last_posted_at) < ?", [$today->toDateString()]);
            })
            ->get()
            ->filter(function ($template) use ($today) {
                return match ($template->frequency) {
                    'daily' => true,
                    'weekly' => $template->last_posted_at?->copy()->addWeek()->isSameDay($today) ?? true,
                    'monthly' => $template->last_posted_at?->copy()->addMonth()->isSameDay($today) ?? true,
                    'yearly' => $template->last_posted_at?->copy()->addYear()->isSameDay($today) ?? true,
                    default => false,
                };
            });
    }
}