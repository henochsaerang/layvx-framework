<?php

namespace App\Core;

class App {
    /**
     * @var Container
     */
    protected static $container;

    /**
     * Set the shared container instance.
     *
     * @param Container $container
     * @return void
     */
    public static function setContainer(Container $container) {
        static::$container = $container;
    }

    /**
     * Get the shared container instance.
     *
     * @return Container
     */
    public static function getContainer() {
        return static::$container;
    }
}
