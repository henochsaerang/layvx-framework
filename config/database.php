<?php

// config/database.php

return [
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'db_user' => $_ENV['DB_USERNAME'] ?? 'root',
    'db_pass' => $_ENV['DB_PASSWORD'] ?? '',
    'db_name' => $_ENV['DB_DATABASE'] ?? 'weblms'
];
