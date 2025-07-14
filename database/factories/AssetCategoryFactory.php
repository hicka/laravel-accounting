<?php

namespace Database\Factories;

use Hickr\Accounting\Models\AssetCategory;
use Hickr\Accounting\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetCategoryFactory extends Factory
{
    protected $model = AssetCategory::class;

    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'name' => $this->faker->words(2, true),
            'method' => 'straight_line', // or 'reducing_balance'
            'useful_life_years' => $this->faker->numberBetween(3, 10),
            'residual_percentage' => $this->faker->randomElement([0, 5, 10]),
            'asset_account_id' => 1,
            'depreciation_expense_account_id' => 1,
            'accum_depreciation_account_id' => 2,
        ];
    }

    public function reducingBalance(): static
    {
        return $this->state(['method' => 'reducing_balance']);
    }
}