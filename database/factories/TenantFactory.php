<?php

namespace Database\Factories;

use Hickr\Accounting\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'region_module' => 'global',
            'base_currency' => 'MVR',
        ];
    }
}