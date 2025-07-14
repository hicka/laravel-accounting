<?php

namespace Database\Factories;

use Hickr\Accounting\Models\FixedAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

class FixedAssetFactory extends Factory
{
    protected $model = FixedAsset::class;

    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'category_id' => \Hickr\Accounting\Models\AssetCategory::factory(),
            'name' => $this->faker->word(),
            'purchase_cost' => 10000,
            'purchase_date' => now()->subMonths(6),
            'start_depreciation_date' => now()->subMonths(3),
            'residual_value' => 1000,
        ];
    }
}