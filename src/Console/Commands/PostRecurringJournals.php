<?php

namespace Hickr\Accounting\Console\Commands;

use Illuminate\Console\Command;
use Hickr\Accounting\Models\JournalTemplate;
use Hickr\Accounting\Actions\PostRecurringJournalTemplateAction;
use Symfony\Component\Console\Command\Command as CommandAlias;

class PostRecurringJournals extends Command
{
    protected $signature = 'accounting:post-recurring-journals';
    protected $description = 'Post due recurring journal templates for all tenants';

    public function handle(): int
    {
        $now = now()->toDateString();

        $templates = JournalTemplate::query()
            ->where('is_recurring', true)
            ->where('auto_post', true)
            ->where('start_date', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })
            ->get();

        $postedCount = 0;

        foreach ($templates as $template) {
            if ($this->shouldPostToday($template)) {
                PostRecurringJournalTemplateAction::execute($template);
                $postedCount++;
            }
        }

        $this->info("Posted $postedCount recurring journals.");
        return CommandAlias::SUCCESS;
    }

    protected function shouldPostToday(JournalTemplate $template): bool
    {
        if (!$template->last_posted_at) return true;

        $last = \Illuminate\Support\Carbon::parse($template->last_posted_at);
        $today = now();

        return match ($template->frequency) {
            'daily'   => $last->lt($today->startOfDay()),
            'weekly'  => $last->lt($today->copy()->startOfWeek()),
            'monthly' => $last->lt($today->copy()->startOfMonth()),
            'yearly'  => $last->lt($today->copy()->startOfYear()),
            default   => false,
        };
    }
}