<?php

namespace Database\Factories;

use Hickr\Accounting\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'customer_id' => 1,
            'amount' => 1000.00,
            'currency_code' => 'MVR',
            'date' => now()->toDateString(),
        ];
    }
}
