<?php

namespace Database\Factories;

use Hickr\Accounting\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
        ];
    }
}
