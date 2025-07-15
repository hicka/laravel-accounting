<?php

namespace Database\Factories;

use Hickr\Accounting\Models\InvoiceLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceLineFactory extends Factory
{
    protected $model = InvoiceLine::class;

    public function definition(): array
    {
        return [
            'invoice_id' => 1,
            'description' => $this->faker->sentence,
            'quantity' => 1,
            'unit_price' => 1000.00,
            'total' => 1000.00,
        ];
    }
}
