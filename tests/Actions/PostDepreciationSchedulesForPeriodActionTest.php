<?php

namespace Hickr\Accounting\Tests\Actions;

use Hickr\Accounting\Models\Tenant;
use Hickr\Accounting\Models\FixedAsset;
use Hickr\Accounting\Models\AssetCategory;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\DepreciationSchedule;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Actions\PostDepreciationSchedulesForPeriodAction;
use Hickr\Accounting\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostDepreciationSchedulesForPeriodActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_bulk_posts_depreciation_schedules_for_given_period()
    {
        $tenant = Tenant::factory()->create();

        $expense = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id, 'type' => 'expense']);
        $accum   = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id, 'type' => 'asset']);
        $asset   = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id, 'type' => 'asset']);

        $category = AssetCategory::factory()->create([
            'tenant_id' => $tenant->id,
            'method' => 'straight_line',
            'useful_life_years' => 5,
            'residual_percentage' => 10,
            'asset_account_id' => $asset->id,
            'depreciation_expense_account_id' => $expense->id,
            'accum_depreciation_account_id' => $accum->id,
        ]);

        $fixedAsset = FixedAsset::factory()->create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'purchase_cost' => 10000,
        ]);

        // Create 2 schedules for the same period
        DepreciationSchedule::factory()->create([
            'tenant_id' => $tenant->id,
            'fixed_asset_id' => $fixedAsset->id,
            'amount' => 200,
            'date' => now()->startOfMonth()->toDateString(),
            'period' => now()->startOfMonth()->toDateString(),
            'posted' => false,
        ]);

        DepreciationSchedule::factory()->create([
            'tenant_id' => $tenant->id,
            'fixed_asset_id' => $fixedAsset->id,
            'amount' => 300,
            'date' => now()->startOfMonth()->toDateString(),
            'period' => now()->startOfMonth()->toDateString(),
            'posted' => false,
        ]);

        $result = PostDepreciationSchedulesForPeriodAction::execute([
            'tenant_id' => $tenant->id,
            'period' => now()->startOfMonth()->toDateString(),
        ]);

        $this->assertCount(2, $result);
        $this->assertEquals(2, JournalEntry::where('tenant_id', $tenant->id)->count());
        $this->assertTrue($result->every(fn($entry) => $entry instanceof JournalEntry));

        $this->assertDatabaseHas('journal_lines', [
            'account_id' => $expense->id,
            'type' => 'debit',
            'amount' => 200,
        ]);

        $this->assertDatabaseHas('journal_lines', [
            'account_id' => $accum->id,
            'type' => 'credit',
            'amount' => 300,
        ]);
    }
}