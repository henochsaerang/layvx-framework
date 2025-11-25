<?php

namespace App\Core;

class Route {
    /**
     * The router instance.
     * @var Router
     */
    protected static $router;

    /**
     * Register a GET route.
     *
     * @param string $uri
     * @param array|callable $action
     * @return void
     */
    public static function get(string $uri, $action) {
        self::getRouter()->get($uri, $action);
    }

    /**
     * Register a POST route.
     *
     * @param string $uri
     * @param array|callable $action
     * @return void
     */
    public static function post(string $uri, $action) {
        self::getRouter()->post($uri, $action);
    }

    /**
     * Get the router instance from the container.
     *
     * @return Router
     */
    protected static function getRouter(): Router {
        if (!static::$router) {
            static::$router = app(Router::class);
        }
        return static::$router;
    }

    /**
     * A helper method to be called from the main entry point to load route files.
     *
     * @param string $path
     * @return void
     */
    public static function load(string $path) {
        require_once $path;
    }
}
