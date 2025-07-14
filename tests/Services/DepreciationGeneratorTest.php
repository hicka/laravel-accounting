<?php

namespace Hickr\Accounting\Tests\Services;

use Hickr\Accounting\Models\FixedAsset;
use Hickr\Accounting\Models\AssetCategory;
use Hickr\Accounting\Models\DepreciationSchedule;
use Hickr\Accounting\Services\DepreciationGenerator;
use Hickr\Accounting\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class DepreciationGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_straight_line_depreciation_schedule()
    {
        $category = AssetCategory::factory()->create([
            'method' => 'straight_line',
            'useful_life_years' => 5,
            'residual_percentage' => 10,
        ]);

        $asset = FixedAsset::factory()->create([
            'category_id' => $category->id,
            'purchase_cost' => 10000,
            'start_depreciation_date' => now()->subMonths(3),
        ]);

        DepreciationGenerator::run($asset);

        $this->assertCount(4, DepreciationSchedule::all()); // includes current month
        $this->assertEquals(150.00, DepreciationSchedule::first()->amount); // 10000 - 10% = 9000 / 5 / 12
    }

    public function test_it_generates_reducing_balance_depreciation_schedule()
    {
        $category = AssetCategory::factory()->create([
            'method' => 'reducing_balance',
            'useful_life_years' => 5,
            'residual_percentage' => 10,
        ]);

        $asset = FixedAsset::factory()->create([
            'category_id' => $category->id,
            'purchase_cost' => 10000,
            'start_depreciation_date' => now()->subMonths(3),
        ]);

        DepreciationGenerator::run($asset);

        $this->assertGreaterThan(0, DepreciationSchedule::count());
        $this->assertLessThan(10000, DepreciationSchedule::sum('amount'));
    }
}
