<?php

namespace Hickr\Accounting\Models;

use Illuminate\Database\Eloquent\Model;

class JournalTemplate extends Model
{
    protected $table = 'journal_templates';

    protected $guarded = [];

    public function lines()
    {
        return $this->hasMany(JournalTemplateLine::class, 'template_id');
    }
}