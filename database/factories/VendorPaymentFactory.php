<?php

namespace Database\Factories;

use Hickr\Accounting\Models\VendorPayment;
use Hickr\Accounting\Models\Vendor;
use Hickr\Accounting\Models\Bill;
use Hickr\Accounting\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorPaymentFactory extends Factory
{
    protected $model = VendorPayment::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'vendor_id' => Vendor::factory(),
            'bill_id' => null, // Can override in tests
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'date' => now()->toDateString(),
            'notes' => $this->faker->optional()->sentence,
        ];
    }

    public function withBill(): static
    {
        return $this->state(function (array $attributes) {
            $bill = Bill::factory()->create([
                'tenant_id' => $attributes['tenant_id'] ?? Tenant::factory(),
                'vendor_id' => $attributes['vendor_id'] ?? Vendor::factory(),
            ]);

            return [
                'bill_id' => $bill->id,
                'vendor_id' => $bill->vendor_id,
                'tenant_id' => $bill->tenant_id,
            ];
        });
    }
}