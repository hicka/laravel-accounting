<?php

namespace Hickr\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tenants'; // or 'businesses' based on config/accounting.php

    protected $fillable = [
        'name',
        'email',
        'region_module',
        // other tenant fields like 'currency_id', 'timezone', etc.
    ];

    protected $casts = [
        'region_module' => 'string',
    ];

    /**
     * Accessor for region module (e.g., 'mira', 'global')
     */
    public function getRegionModule(): string
    {
        return $this->region_module ?? 'global';
    }

    /**
     * (Optional) Static resolver used by RegionResolver
     * Customize this to suit your multi-tenancy implementation.
     */
    public static function resolveCurrent(): ?self
    {
        // Example: use a tenancy package like Tenancy::getTenant()
        // Or custom session-based resolver
        return auth()->user()?->tenant ?? null;
    }

    /**
     * Example: relation to journals (future feature)
     */
    public function journalEntries()
    {
        return $this->hasMany(\Hickr\Accounting\Models\JournalEntry::class, 'tenant_id');
    }

    /**
     * Example: relation to ledgers (future feature)
     */
    public function ledgers()
    {
        return $this->hasMany(\Hickr\Accounting\Models\Ledger::class, 'tenant_id');
    }
}