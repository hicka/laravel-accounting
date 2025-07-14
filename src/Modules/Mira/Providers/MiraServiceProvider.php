<?php

namespace Hickr\Accounting\Modules\Mira\Providers;

use Illuminate\Support\ServiceProvider;

class MiraServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/mira.php', 'accounting.mira');
    }
}