<?php

namespace Hickr\Accounting\Tests\Actions;

use Hickr\Accounting\Actions\PostJournalTemplateAction;
use Hickr\Accounting\Models\ChartOfAccount;
use Hickr\Accounting\Models\JournalTemplate;
use Hickr\Accounting\Models\JournalTemplateLine;
use Hickr\Accounting\Models\Tenant;
use Hickr\Accounting\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostJournalTemplateActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_posts_a_journal_template()
    {
        $tenant = Tenant::factory()->create(['base_currency' => 'MVR']);

        $cash = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => ChartOfAccount::TYPE_ASSET,
        ]);

        $revenue = ChartOfAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'type' => ChartOfAccount::TYPE_REVENUE,
        ]);

        $template = JournalTemplate::create([
            'tenant_id' => $tenant->id,
            'name' => 'Monthly Rent',
            'description' => 'Recurring rent transaction',
            'currency_code' => 'MVR',
            'exchange_rate' => 1,
            'inverse' => false,
        ]);

        JournalTemplateLine::create([
            'template_id' => $template->id,
            'account_id' => $cash->id,
            'type' => 'credit',
            'amount' => 5000.00,
        ]);

        JournalTemplateLine::create([
            'template_id' => $template->id,
            'account_id' => $revenue->id,
            'type' => 'debit',
            'amount' => 5000.00,
        ]);

        $entry = PostJournalTemplateAction::execute($template->id);

        $this->assertEquals(2, $entry->lines()->count());
        $this->assertEquals('Recurring rent transaction', $entry->description);
        $this->assertEquals('MVR', $entry->currency_code);
    }
}
