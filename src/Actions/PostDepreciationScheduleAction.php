<?php
namespace Hickr\Accounting\Actions;

use Hickr\Accounting\Models\DepreciationSchedule;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PostDepreciationScheduleAction
{
    public static function execute(DepreciationSchedule $schedule): JournalEntry
    {
        $asset = $schedule->asset;
        $category = $asset->category;

        $tenantId = $schedule->tenant_id;

        return DB::transaction(function () use ($schedule, $asset, $category, $tenantId) {
            $entry = JournalEntry::create([
                'tenant_id' => $tenantId,
                'date' => Carbon::parse($schedule->period)->endOfMonth()->toDateString(),
                'description' => "Depreciation for asset: {$asset->name}",
                'currency_code' => 'MVR', // You can derive this from tenant config
                'exchange_rate' => 1,
                'base_currency_amount' => $schedule->amount,
            ]);

            $entry->lines()->createMany([
                [
                    'tenant_id' => $tenantId,
                    'account_id' => $category->depreciation_expense_account_id,
                    'type' => 'debit',
                    'amount' => $schedule->amount,
                    'base_currency_amount' => $schedule->amount,
                    'meta' => ['fixed_asset_id' => $asset->id],
                ],
                [
                    'tenant_id' => $tenantId,
                    'account_id' => $category->accum_depreciation_account_id,
                    'type' => 'credit',
                    'amount' => $schedule->amount,
                    'base_currency_amount' => $schedule->amount,
                    'meta' => ['fixed_asset_id' => $asset->id],
                ],
            ]);

            $schedule->update(['posted' => true]);

            return $entry;
        });
    }
}