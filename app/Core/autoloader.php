<?php

// app/Core/autoloader.php

/**
 * Custom Autoloader (PSR-4 like)
 * This function will automatically load class files based on their namespace and class name.
 */
spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = 'App\\';

    // Base directory for the namespace prefix
    $base_dir = __DIR__ . '/../'; // Points to the 'app' directory

    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, move to the next registered autoloader
        return;
    }

    // Get the relative class name (e.g., Controllers\HomeController)
    $relative_class = substr($class, $len);

    // Replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // error_log("Autoloader trying to load: " . $class . " from " . $file); // DIKOMENTARI

    // If the file exists, require it
    if (file_exists($file)) {
        require_once $file;
        // error_log("Autoloader SUCCESS: Loaded " . $class); // DIKOMENTARI
    } else {
        // error_log("Autoloader FAIL: File not found for " . $class . " at " . $file); // DIKOMENTARI
    }
});