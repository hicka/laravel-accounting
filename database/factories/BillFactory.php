<?php

namespace Database\Factories;

use Hickr\Accounting\Models\Bill;
use Hickr\Accounting\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillFactory extends Factory
{
    protected $model = Bill::class;

    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'vendor_id' => Vendor::factory(),
            'reference' => strtoupper($this->faker->bothify('BILL-####')),
            'date' => now()->subDays(rand(1, 30)),
            'due_date' => now()->addDays(rand(5, 30)),
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'total' => 1000.00,
            'balance' => 1000.00,
        ];
    }
}
