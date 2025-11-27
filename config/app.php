<?php

// config/app.php

return [
    /**
     * Application Name
     */
    'name' => 'LayVX Framework',

    /**
     * Application Environment
     */
    'env' => $_ENV['APP_ENV'] ?? 'production',

    /**
     * Autoloaded Service Providers
     *
     * Daftarkan AppServiceProvider di sini agar otomatis dimuat
     * oleh kernel aplikasi saat HTTP request atau CLI command dijalankan.
     */
    'providers' => [
        \App\Providers\AppServiceProvider::class,
    ],
];