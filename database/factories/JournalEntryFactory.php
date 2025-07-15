<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\Tenant;

class JournalEntryFactory extends Factory
{
    protected $model = JournalEntry::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'reference' => $this->faker->uuid,
            'description' => $this->faker->sentence,
            'date' => $this->faker->date(),
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'base_currency_amount' => $this->faker->randomFloat(2, 100, 10000),
            'status' => 'draft',
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(function () {
            return [
                'status' => 'approved',
                'approved_by' => 1,
                'approved_at' => now(),
            ];
        });
    }
}