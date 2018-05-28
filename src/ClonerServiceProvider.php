<?php

namespace Anfischer\Cloner;

use Illuminate\Support\ServiceProvider;

class ClonerServiceProvider extends ServiceProvider
{
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
    }
}
