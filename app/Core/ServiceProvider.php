<?php

namespace App\Core;

abstract class ServiceProvider {
    /**
     * The service container instance.
     *
     * @var \App\Core\Container
     */
    protected $container;

    /**
     * Create a new service provider instance.
     *
     * @param  \App\Core\Container  $container
     * @return void
     */
    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    abstract public function register();
}
