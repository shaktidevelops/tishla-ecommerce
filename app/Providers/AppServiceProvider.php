<?php

namespace App\Providers;

use IlluminateSupportFacadesURL;
use IlluminateSupportServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
