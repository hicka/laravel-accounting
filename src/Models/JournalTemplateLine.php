<?php

namespace Hickr\Accounting\Models;

use Illuminate\Database\Eloquent\Model;

class JournalTemplateLine extends Model
{
    protected $guarded = [];

    protected $table = 'journal_template_lines';

    protected $fillable = [
      'tenant_id',
      'account_id',
      'amount',
      'type',
      'template_id'
    ];

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}