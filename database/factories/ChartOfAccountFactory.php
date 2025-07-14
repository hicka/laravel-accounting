<?php

namespace Database\Factories;

use Hickr\Accounting\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChartOfAccountFactory extends Factory
{
    protected $model = ChartOfAccount::class;

    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'code' => $this->faker->unique()->numerify('1###'),
            'name' => $this->faker->words(2, true),
            'type' => $this->faker->randomElement(['asset', 'liability', 'equity', 'revenue', 'expense']),
        ];
    }
}