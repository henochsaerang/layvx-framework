<?php
// app/Core/helpers.php

// Define global arrays to store routes
// These will be populated by the get(), post() etc. helper functions
// and then used by public/index.php
global $routes;
$routes = [
    'GET' => [],
    'POST' => [],
    // Add other methods if needed
];

global $namedRoutes; // New global array for named routes (name => uri)
$namedRoutes = [];

// --- RouteBuilder class for method chaining ---
// This class will allow syntax like get(...)->name(...)
class RouteBuilder {
    private $method;
    private $uri;
    private $action;
    private $middleware; // Property to hold the middleware

    public function __construct(string $method, string $uri, array $action) {
        $this->method = $method;
        $this->uri = $uri;
        $this->action = $action;
        $this->middleware = null; // Initialize middleware as null

        // Immediately add the route to the global routes array
        global $routes;
        // The action array will be updated with middleware later if provided
        $routes[$this->method][$this->uri] = $this->action;
    }

    /**
     * Assign a middleware to the current route.
     *
     * @param string $middleware The middleware class name.
     * @return $this
     */
    public function middleware(string $middleware): self {
        $this->middleware = $middleware;

        // Update the global route entry with the middleware
        global $routes;
        if (isset($routes[$this->method][$this->uri])) {
            // Store middleware as the third element in the action array
            $routes[$this->method][$this->uri][] = $this->middleware;
        }

        return $this; // Allow chaining
    }

    /**
     * Assign a name to the current route.
     *
     * @param string $name The unique name for the route.
     * @return $this
     * @throws Exception if the route name is already defined.
     */
    public function name(string $name): self {
        global $namedRoutes;
        if (isset($namedRoutes[$name])) {
            throw new Exception("Route name '{$name}' is already defined.");
        }
        $namedRoutes[$name] = $this->uri;
        return $this; // Allow chaining
    }
}

if (!function_exists('get')) {
    /**
     * Define a GET route.
     *
     * @param string $uri The URI pattern.
     * @param array $action The controller and method to call.
     * @return RouteBuilder
     */
    function get(string $uri, array $action): RouteBuilder {
        return new RouteBuilder('GET', $uri, $action);
    }
}

if (!function_exists('post')) {
    /**
     * Define a POST route.
     *
     * @param string $uri The URI pattern.
     * @param array $action The controller and method to call.
     * @return RouteBuilder
     */
    function post(string $uri, array $action): RouteBuilder {
        return new RouteBuilder('POST', $uri, $action);
    }
}

// --- New helper function to generate URI from route name ---
if (!function_exists('route')) {
    /**
     * Generate a URL for a given named route.
     *
     * @param string $name The name of the route.
     * @param array $params Optional. Key-value pairs for query parameters.
     * @return string The generated URL.
     * @throws Exception if the named route is not found.
     */
    function route(string $name, array $params = []): string {
        global $namedRoutes;
        if (!isset($namedRoutes[$name])) {
            throw new Exception("Named route '{$name}' not found.");
        }
        $uri = $namedRoutes[$name];
        if (!empty($params)) {
            $uri .= (strpos($uri, '?') === false ? '?' : '&') . http_build_query($params);
        }
        return $uri;
    }
}


// --- Existing tuama_field and render functions below ---

if (!function_exists('tuama_field')) {
    /**
     * Generate a hidden HTML input field for Tuama (CSRF) token protection.
     */
    function tuama_field() {
        $token = $_SESSION['tuama_token'] ?? '';
        echo '<input type="hidden" name="tuama_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('render')) {
    /**
     * Compile and render a view.
     *
     * @param string $view The name of the view (e.g., 'auth/login').
     * @param array $data The data to be extracted into variables for the view.
     */
    function render(string $view, array $data = []) {
        // Correctly resolve the base path for views relative to the project root
        $basePath = __DIR__ . '/../../';
        $viewPath = $basePath . 'views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new Exception("View not found: {$viewPath}");
        }

        $compiledDir = $basePath . 'storage/framework/views/';
        $compiledPath = $compiledDir . sha1($viewPath) . '.php';

        // Read the original view content
        $content = file_get_contents($viewPath);

        // Compile the view: replace @tuama
        $compiledContent = str_replace('@tuama', '<?php tuama_field(); ?>', $content);

        // Save the compiled view
        file_put_contents($compiledPath, $compiledContent);

        // Extract data and include the compiled view
        extract($data);
        
        require $compiledPath;
    }
}

// --- Migration Helper Classes and Functions ---

// Required for type hinting in Migration classes
require_once __DIR__ . '/Migration.php';

// Global helper function to create a Column instance
if (!function_exists('col')) {
    function col(string $name = null): App\Core\Column {
        return new App\Core\Column($name);
    }
}

// Global function to define special "timestamps" columns
if (!function_exists('timestamps')) {
    function timestamps(): array {
        return [
            col('created_at')->timestamp()->default('CURRENT_TIMESTAMP'),
            col('updated_at')->timestamp()->default('CURRENT_TIMESTAMP')->onUpdate('CURRENT_TIMESTAMP'),
        ];
    }
}

if (!function_exists('time_ago')) {
    /**
     * Fungsi untuk mengubah timestamp menjadi format "time ago".
     *
     * @param string $timestamp Timestamp dalam format yang dapat dipahami strtotime().
     * @return string String "time ago" (misalnya "5 minutes ago").
     */
    function time_ago($timestamp) {
        $time_ago = strtotime($timestamp);
        $current_time = time();
        $time_difference = $current_time - $time_ago;
        $seconds = $time_difference;
        $minutes      = round($seconds / 60);
        $hours        = round($seconds / 3600);
        $days         = round($seconds / 86400);
        $weeks        = round($seconds / 604800);
        $months       = round($seconds / 2629440);
        $years        = round($seconds / 31553280);
        if ($seconds <= 60) { return "Just Now"; }
        else if ($minutes <= 60) { return ($minutes == 1) ? "1 minute ago" : "$minutes minutes ago"; }
        else if ($hours <= 24) { return ($hours == 1) ? "1 hour ago" : "$hours hours ago"; }
        else if ($days <= 7) { return ($days == 1) ? "1 day ago" : "$days days ago"; }
        else if ($weeks <= 4.3) { return ($weeks == 1) ? "1 week ago" : "$weeks weeks ago"; }
        else if ($months <= 12) { return ($months == 1) ? "1 month ago" : "$months months ago"; }
        else { return ($years == 1) ? "1 year ago" : "$years years ago"; }
    }
}