<?php
namespace Hickr\Accounting\Services;

use Hickr\Accounting\Models\FixedAsset;
use Hickr\Accounting\Models\DepreciationSchedule;
use Illuminate\Support\Carbon;

class DepreciationGenerator
{
    public static function run(FixedAsset $asset): void
    {
        $category = $asset->category;
        $startDate = $asset->start_depreciation_date->copy()->startOfMonth();
        $endPeriod = now()->startOfMonth();
        $residual = $asset->residual_value;
        $tenantId = $asset->tenant_id;

        $existingPeriods = $asset->schedules()->pluck('period')->map(fn($p) => Carbon::parse($p)->toDateString())->toArray();

        $period = $startDate->copy();
        $schedule = [];

        if ($category->method === 'straight_line') {
            $totalDepreciable = $asset->purchase_cost - $residual;
            $annual = $totalDepreciable / $category->useful_life_years;
            $monthly = $annual / 12;

            while ($period->lte($endPeriod)) {
                if (in_array($period->toDateString(), $existingPeriods)) {
                    $period->addMonth();
                    continue;
                }

                $schedule[] = [
                    'tenant_id' => $tenantId,
                    'fixed_asset_id' => $asset->id,
                    'period' => $period->toDateString(),
                    'amount' => round($monthly, 2),
                    'posted' => false,
                ];

                $period->addMonth();
            }
        } elseif ($category->method === 'reducing_balance') {
            $rate = 1 / $category->useful_life_years; // e.g., 0.20 = 20% per year
            $current_value = $asset->purchase_cost;

            while ($period->lte($endPeriod) && $current_value > $residual) {
                if (in_array($period->toDateString(), $existingPeriods)) {
                    $period->addMonth();
                    continue;
                }

                $monthly_depr = (($current_value * $rate) / 12);

                // Ensure we don’t depreciate below residual
                if (($current_value - $monthly_depr) < $residual) {
                    $monthly_depr = $current_value - $residual;
                }

                $schedule[] = [
                    'tenant_id' => $tenantId,
                    'fixed_asset_id' => $asset->id,
                    'period' => $period->toDateString(),
                    'amount' => round($monthly_depr, 2),
                    'posted' => false,
                ];

                $current_value -= $monthly_depr;
                $period->addMonth();
            }
        }

        // Bulk insert
        DepreciationSchedule::insert($schedule);
    }
}