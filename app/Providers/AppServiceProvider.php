<?php

namespace App\Providers;

use App\Services\CongregationHubApi;
use App\Support\ChurchContext;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(CongregationHubApi::class, function ($app) {
            return new CongregationHubApi($app->make(ChurchContext::class));
        });
    }

    public function boot(): void
    {
        //
    }
}
