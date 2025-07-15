<?php

namespace Hickr\Accounting;

use Hickr\Accounting\Console\Commands\PostRecurringJournals;
use Illuminate\Support\ServiceProvider;

class AccountingServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/accounting.php', 'accounting');
        $this->commands([
            PostRecurringJournals::class,
        ]);
    }

    public function boot()
    {
        $region = config('accounting.region_module');

        if ($region === 'mira') {
            $this->app->register(\Hickr\Accounting\Modules\Mira\Providers\MiraServiceProvider::class);
        }

        $this->publishes([
            __DIR__ . '/../config/accounting.php' => config_path('accounting.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}