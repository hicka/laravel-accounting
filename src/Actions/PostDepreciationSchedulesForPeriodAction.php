<?php
namespace Hickr\Accounting\Actions;

use Hickr\Accounting\Models\DepreciationSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PostDepreciationSchedulesForPeriodAction
{
    /**
     * @return \Illuminate\Support\Collection<\Hickr\Accounting\Models\JournalEntry>
     */
    public static function execute(int $tenantId, string $from, string $to): Collection
    {
        $schedules = DepreciationSchedule::query()
            ->where('tenant_id', $tenantId)
            ->whereBetween('period', [Carbon::parse($from)->startOfMonth(), Carbon::parse($to)->endOfMonth()])
            ->where('posted', false)
            ->with(['asset.category'])
            ->get();

        $entries = collect();

        foreach ($schedules as $schedule) {
            $entries->push(
                PostDepreciationScheduleAction::execute($schedule)
            );
        }

        return $entries;
    }
}