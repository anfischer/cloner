<?php

namespace Anfischer\Cloner;

use Illuminate\Support\ServiceProvider;

class ClonerServiceProvider extends ServiceProvider
{
    /**
    * Bootstrap any application services.
    *
    * @return void
    */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/cloner.php' => config_path('cloner.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Cloner::class, function () {
            return new Cloner(new CloneService, new PersistenceService);
        });

        $this->app->alias(Cloner::class, 'cloner');

        $this->registerConfiguration();
    }

    /**
    * Register the configuration required for the package.
    *
    * @return void
    */
    public function registerConfiguration()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/cloner.php',
            'cloner'
        );
    }
}
