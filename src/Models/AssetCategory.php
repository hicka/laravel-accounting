<?php
namespace Hickr\Accounting\Models;

use Database\Factories\AssetCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'useful_life_years',
        'residual_percentage',
        'method',
        'asset_account_id',
        'accum_depreciation_account_id',
        'depreciation_expense_account_id',
    ];

    protected static function newFactory()
    {
        return AssetCategoryFactory::new();
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assets()
    {
        return $this->hasMany(FixedAsset::class, 'category_id');
    }
}