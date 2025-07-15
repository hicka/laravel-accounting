<?php

namespace Database\Factories;

use Hickr\Accounting\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'customer_id' => 1,
            'invoice_number' => $this->faker->unique()->numerify('INV-#####'),
            'total' => 1000,
            'balance' => 1000,
            'status' => 'unpaid',
            'currency_code' => 'MVR',
            'date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
        ];
    }
}
