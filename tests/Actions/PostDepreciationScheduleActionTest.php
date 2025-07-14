<?php

namespace Hickr\Accounting\Tests\Actions;

use Hickr\Accounting\Models\Tenant;
use Hickr\Accounting\Models\FixedAsset;
use Hickr\Accounting\Models\AssetCategory;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\DepreciationSchedule;
use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Actions\PostDepreciationScheduleAction;
use Hickr\Accounting\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostDepreciationScheduleActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_posts_a_depreciation_schedule_to_journal()
    {
        $tenant = Tenant::factory()->create();

        $expenseAccount = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id, 'type' => 'expense']);
        $accumAccount   = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id, 'type' => 'asset']);
        $assetAccount   = ChartOfAccount::factory()->create(['tenant_id' => $tenant->id, 'type' => 'asset']);

        $category = AssetCategory::factory()->create([
            'tenant_id' => $tenant->id,
            'method' => 'straight_line',
            'useful_life_years' => 5,
            'residual_percentage' => 10,
            'asset_account_id' => $assetAccount->id,
            'depreciation_expense_account_id' => $expenseAccount->id,
            'accum_depreciation_account_id' => $accumAccount->id,
        ]);

        $asset = FixedAsset::factory()->create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'purchase_cost' => 10000,
        ]);

        $schedule = DepreciationSchedule::create([
            'tenant_id' => $tenant->id,
            'fixed_asset_id' => $asset->id,
            'date' => now()->toDateString(),
            'period' => now()->startOfMonth()->toDateString(),
            'amount' => 150,
            'posted' => false,
        ]);

        $entry = PostDepreciationScheduleAction::execute($schedule);

        $this->assertInstanceOf(JournalEntry::class, $entry);
        $this->assertEquals($tenant->id, $entry->tenant_id);
        $this->assertEquals(2, $entry->lines->count());

        $this->assertDatabaseHas('journal_lines', [
            'account_id' => $expenseAccount->id,
            'type' => 'debit',
            'amount' => 150,
        ]);

        $this->assertDatabaseHas('journal_lines', [
            'account_id' => $accumAccount->id,
            'type' => 'credit',
            'amount' => 150,
        ]);

        $this->assertTrue($schedule->fresh()->posted);
    }
}