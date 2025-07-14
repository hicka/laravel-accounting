<?php
namespace Hickr\Accounting\Actions;

use Hickr\Accounting\Models\DepreciationSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PostDepreciationSchedulesForPeriodAction
{
    /**
     * @param array $data
     * @return \Illuminate\Support\Collection<\Hickr\Accounting\Models\JournalEntry>
     */
    public static function execute(array $data): Collection
    {
        $tenantId = $data['tenant_id'];
        $from = $data['from'] ?? $data['period'];
        $to = $data['to'] ?? $data['period'];

        $schedules = DepreciationSchedule::query()
            ->where('tenant_id', $tenantId)
            ->whereBetween('period', [Carbon::parse($from)->startOfMonth(), Carbon::parse($to)->endOfMonth()])
            ->where('posted', false)
            ->with(['asset.category'])
            ->get();

        return $schedules->map(fn($schedule) =>
        PostDepreciationScheduleAction::execute($schedule)
        );
    }
}