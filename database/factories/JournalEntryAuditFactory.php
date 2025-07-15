<?php

namespace Database\Factories;

use Hickr\Accounting\Models\JournalEntry;
use Hickr\Accounting\Models\JournalEntryAudit;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalEntryAuditFactory extends Factory
{
    protected $model = JournalEntryAudit::class;

    public function definition(): array
    {
        return [
            'journal_entry_id' => JournalEntry::factory(),
            'user_id' => null,
            'action' => 'created',
            'changes' => ['example' => 'change'],
        ];
    }
}