<?php

namespace Database\Factories;

use Hickr\Accounting\Models\AssetCategory;
use Hickr\Accounting\Models\DepreciationSchedule;
use Hickr\Accounting\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepreciationScheduleFactory extends Factory
{
    protected $model = DepreciationSchedule::class;

    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'fixed_asset_id' => 1,
            'date' => now()->toDateString(),
            'period' => now()->startOfMonth()->toDateString(),
            'amount' => 100,
            'posted' => false,
        ];
    }

}