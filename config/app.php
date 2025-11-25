<?php

// config/app.php

return [
    /**
     * Application Name
     */
    'name' => 'LayVX Framework',

    /**
     * Application Environment
     *
     * This value determines the "environment" your application is currently
     * running in. This may determine how some parts of the application
     * behave. Valid values include "development", "production", "testing".
     */
    'env' => $_ENV['APP_ENV'] ?? 'production',

    /**
     * Autoloaded Service Providers
     *
     * These service providers are automatically loaded on every request
     * to your application. Feel free to add your own services to
     * this array to grant expanded functionality to your applications.
     */
    'providers' => [
        \App\Providers\AppServiceProvider::class,
    ],
];