<?php

namespace Hickr\Accounting\Models;

use Database\Factories\VendorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'name', 'email', 'phone'
    ];

    protected static function newFactory()
    {
        return VendorFactory::new();
    }
}
