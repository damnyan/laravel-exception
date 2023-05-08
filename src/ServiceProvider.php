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
        $this->loadRoutesFrom(__DIR__ . '/../routes/reference.php');
    }
}
