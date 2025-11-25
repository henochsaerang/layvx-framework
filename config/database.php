<?php

// config/database.php

return [
    /**
     * Default Database Connection Name
     */
    'driver' => $_ENV['DB_CONNECTION'] ?? 'mysql',

    /**
     * Database Host
     */
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',

    /**
     * Database Port
     */
    'port' => $_ENV['DB_PORT'] ?? '3306',

    /**
     * Database Name
     */
    'db_name' => $_ENV['DB_DATABASE'] ?? 'layvx_db',

    /**
     * Database Username
     */
    'db_user' => $_ENV['DB_USERNAME'] ?? 'root',

    /**
     * Database Password
     */
    'db_pass' => $_ENV['DB_PASSWORD'] ?? '',

    /**
     * Database Charset
     */
    'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
];

