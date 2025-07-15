<?php
namespace Database\Factories;

use Hickr\Accounting\Models\CustomerCreditBalance;
use Hickr\Accounting\Models\Customer;
use Hickr\Accounting\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerCreditBalanceFactory extends Factory
{
    protected $model = CustomerCreditBalance::class;

    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'customer_id' => Customer::factory(),
            'payment_id' => Payment::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }
}