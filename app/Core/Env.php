<?php

namespace App\Core;

// app/Core/Env.php

class Env {
    /**
     * Loads a .env file from the given directory.
     *
     * @param string $path The directory where the .env file is located.
     */
    public static function load(string $path) {
        $envFile = $path . '/.env';

        if (!is_readable($envFile)) {
            // You could throw an exception here if the .env file is mandatory
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Split into key and value
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Set environment variables
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}
