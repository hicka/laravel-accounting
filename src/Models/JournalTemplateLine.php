<?php

namespace Hickr\Accounting\Models;

use Illuminate\Database\Eloquent\Model;

class JournalTemplateLine extends Model
{
    protected $guarded = [];

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}