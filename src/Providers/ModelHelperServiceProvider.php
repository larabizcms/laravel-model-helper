<?php

namespace LarabizCMS\LaravelModelHelper\Providers;

use Illuminate\Support\ServiceProvider;
use LarabizCMS\LaravelModelHelper\CacheGroup;
use LarabizCMS\LaravelModelHelper\Contracts\CacheGroup as QueriesCacheGroup;

class ModelHelperServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(QueriesCacheGroup::class, fn ($app) => new CacheGroup($app['cache']));
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
