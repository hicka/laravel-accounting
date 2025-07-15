<?php

namespace Hickr\Accounting\Models;

use Illuminate\Database\Eloquent\Model;

class JournalTemplate extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'name',
        'description',
        'is_recurring',
        'frequency',
        'start_date',
        'end_date',
        'last_posted_at',
        'tenant_id'
    ];

    public function lines()
    {
        return $this->hasMany(JournalTemplateLine::class, 'template_id');
    }
}