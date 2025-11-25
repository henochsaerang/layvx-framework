<?php

namespace App\Core;

// app/Core/ErrorHandler.php

class ErrorHandler {
    public static function register() {
        // Tell PHP to report all errors
        error_reporting(E_ALL);

        // Set custom handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError($level, $message, $file, $line) {
        // Convert all PHP errors to exceptions
        throw new \ErrorException($message, 0, $level, $file, $line);
    }

    public static function handleException(\Throwable $exception) {
        http_response_code(500);
        $config = require __DIR__ . '/../../config/app.php'; // Updated path for config

        if ($config['env'] === 'development') {
            // In development, show detailed error page
            self::showDevelopmentErrorPage($exception);
        } else {
            // In production, show generic error and log the details
            self::showProductionErrorPage();
            // You would also log the error to a file here
            // error_log($exception);
        }
        exit();
    }

    public static function handleShutdown() {
        $error = error_get_last();
        // If there was a fatal error, it will be caught here
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            $exception = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            self::handleException($exception);
        }
    }

        private static function showDevelopmentErrorPage(\Throwable $exception) {
            // Pass the entire exception object to the view
            require_once __DIR__ . '/errors/500.php';
        }
    private static function showProductionErrorPage() {
        echo "<h1>500 - Internal Server Error</h1>";
        echo "<p>Something went wrong. Please try again later.</p>";
    }
}
