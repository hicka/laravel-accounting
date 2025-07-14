<?php
namespace Hickr\Accounting\Models;

use Database\Factories\DepreciationScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepreciationSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'fixed_asset_id',
        'period',
        'amount',
        'posted',
    ];

    protected $casts = [
        'period' => 'date',
        'posted' => 'boolean',
    ];

    protected static function newFactory()
    {
        return DepreciationScheduleFactory::new();
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}