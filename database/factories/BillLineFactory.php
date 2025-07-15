<?php

namespace Database\Factories;

use Hickr\Accounting\Models\BillLine;
use Hickr\Accounting\Models\Bill;
use Hickr\Accounting\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillLineFactory extends Factory
{
    protected $model = BillLine::class;

    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'bill_id' => Bill::factory(),
            'account_id' => ChartOfAccount::factory(),
            'description' => $this->faker->sentence,
            'amount' => $this->faker->randomFloat(2, 100, 1000),
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => 1000.00,
        ];
    }
}
