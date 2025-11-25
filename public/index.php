<?php

error_log("public/index.php: Application started.");

// public/index.php

/**
 * Load Environment Variables
 */
require_once '../app/Core/Env.php';
\App\Core\Env::load(__DIR__ . '/..');

/**
 * Register The Autoloader
 */
require_once '../app/Core/autoloader.php';

/**
 * Register The Error Handler
 * This will catch all errors and display them in a formatted way.
 */
require_once '../app/Core/ErrorHandler.php';
\App\Core\ErrorHandler::register();

/**
 * Load Global Helper Functions
 */
require_once '../app/Core/helpers.php';

session_start();

// Initialize Tuama (CSRF) token if it doesn't exist in the session
if (!isset($_SESSION['tuama_token'])) {
    $_SESSION['tuama_token'] = bin2hex(random_bytes(32)); // Generate a secure, random token
}

// 1. Load routes - routes.web.php will use the global get()/post() functions
// Make the global $routes array available here
global $routes;
require '../routes/web.php';

// 2. Get the requested URI and HTTP method
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = $uri === '/' ? '/' : rtrim($uri, '/');
$method = $_SERVER['REQUEST_METHOD']; // Get the HTTP method

// 3. Find a matching route
$action = null;
$params = [];

// Check if there are routes for the current HTTP method
if (isset($routes[$method])) {
    foreach ($routes[$method] as $route => $callback) {
        // Simple route matching, can be improved for dynamic params
        if ($route === $uri) {
            $action = $callback;
            break;
        }
    }
}

// 4. If route is found, execute the controller action
if ($action && is_array($action) && count($action) >= 2) {
    $controllerName = $action[0];
    $methodName = $action[1];

    // --- Middleware Execution ---
    // Check if a middleware is attached to the route (it will be the 3rd element)
    if (isset($action[2])) {
        $middlewareClass = $action[2];
        if (class_exists($middlewareClass)) {
            $middleware = new $middlewareClass();
            $middleware->handle(); // Execute the middleware's handle method
        } else {
            // This will trigger our error handler
            throw new Exception("Middleware class not found: {$middlewareClass}");
        }
    }
    // --- End of Middleware Execution ---

    $fullyQualifiedControllerName = 'App\\Controllers\\' . $controllerName;

    // Create an instance and call the method using the autoloader
    if (class_exists($fullyQualifiedControllerName) && method_exists($fullyQualifiedControllerName, $methodName)) {
        $controller = new $fullyQualifiedControllerName();
        $controller->$methodName($_REQUEST);
    } else {
        // Render 404 (Controller or method not found)
        http_response_code(404);
        require_once '../app/Core/errors/404.php';
    }
} else {
    // Handle 404 Not Found (No matching route found)
    http_response_code(404);
    require_once '../app/Core/errors/404.php';
}
