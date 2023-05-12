<?php

namespace Dmn\Exceptions;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{

    /**
     * {@inheritDoc}
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('dmod_exception.php')
        ], 'dmod-exception-config');

        $this->loadRoutesFrom(__DIR__ . '/../routes/reference.php');
    }
}
