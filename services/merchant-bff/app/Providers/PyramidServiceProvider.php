<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SayaEat\Shared\Clients\PyramidClient;
use SayaEat\Shared\Contracts\PyramidClientInterface;

class PyramidServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PyramidClientInterface::class, function ($app) {
            return new PyramidClient(
                baseUrl: config('pyramid.base_url'),
                apiKey: config('pyramid.api_key'),
                timeout: config('pyramid.timeout'),
                cacheTtl: config('pyramid.cache_ttl'),
                retryTimes: config('pyramid.retry.times'),
                retrySleep: config('pyramid.retry.sleep')
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

