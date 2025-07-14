<?php

namespace Hickr\Accounting\Models;

use Database\Factories\FixedAssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'purchase_cost',
        'purchase_date',
        'residual_value',
        'start_depreciation_date',
        'asset_account_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'purchase_date' => 'date',
        'start_depreciation_date' => 'date',
    ];

    protected static function newFactory()
    {
        return FixedAssetFactory::new();
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function schedules()
    {
        return $this->hasMany(DepreciationSchedule::class);
    }
}