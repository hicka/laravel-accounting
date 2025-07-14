<?php


namespace Hickr\Accounting\Support\Region;

use Hickr\Accounting\Contracts\RegionalModule;
use Illuminate\Database\Eloquent\Model;

class RegionResolver
{
    public static function resolveForTenant($tenant): ?RegionalModule
    {
        $column = config('accounting.region_module_column', 'region_module');
        $region = $tenant?->$column ?? 'global';

        return match ($region) {
            'mira' => app(\Hickr\Accounting\Modules\Mira\MiraModule::class),
            default => null,
        };
    }

    public static function currentTenant(): ?Model
    {
        $tenantModel = config('accounting.tenant_model');
        return app($tenantModel)::resolveCurrent(); // customize this
    }
}