<?php

namespace App\Core;

class Request {
    protected $uri;
    protected $method;
    protected $queryParams = [];
    protected $postParams = [];
    protected $headers = [];
    protected $serverParams = [];
    protected $user = null;

    public function __construct() {
        if (php_sapi_name() === 'cli') {
            // If running in CLI, set safe defaults.
            $this->uri = '/';
            $this->method = 'CLI';
            $this->serverParams = $_SERVER; // Arguments are in $_SERVER['argv']
        } else {
            // If running in a web server environment.
            $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $this->method = $_SERVER['REQUEST_METHOD'];
            $this->queryParams = $_GET;
            $this->postParams = $_POST;
            $this->serverParams = $_SERVER;

            // getallheaders() is not always available, e.g., on Nginx.
            if (function_exists('getallheaders')) {
                $this->headers = getallheaders();
            } else {
                $this->headers = $this->getHeadersFromServer();
            }

            // Handle JSON input
            if (strpos($this->header('Content-Type', ''), 'application/json') !== false) {
                $jsonData = json_decode(file_get_contents('php://input'), true);
                $this->postParams = array_merge($this->postParams, $jsonData ?: []);
            }
        }
    }
    
    /**
     * Fallback to get headers from $_SERVER global if getallheaders() is not available.
     * @return array
     */
    protected function getHeadersFromServer(): array {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headerKey = str_replace('_', '-', substr($key, 5));
                $headers[$headerKey] = $value;
            }
        }
        return $headers;
    }

    /**
     * Create a new request instance from globals.
     * @return static
     */
    public static function capture(): self {
        return new static();
    }

    /**
     * Get the request URI.
     * @return string
     */
    public function uri(): string {
        return $this->uri === '/' ? '/' : rtrim($this->uri, '/');
    }

    /**
     * Get the request method.
     * @return string
     */
    public function method(): string {
        return $this->method;
    }

    /**
     * Get a value from the request query parameters (GET).
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function query(string $key, $default = null) {
        return $this->queryParams[$key] ?? $default;
    }

    /**
     * Get a value from the request body (POST or JSON).
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function input(string $key, $default = null) {
        return $this->postParams[$key] ?? $default;
    }

    /**
     * Get a sanitized value from the request body (POST or JSON) to prevent XSS.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function clean(string $key, $default = null) {
        $value = $this->postParams[$key] ?? $default;
        return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
    }

    /**
     * Get all input from the request body.
     * @return array
     */
    public function all(): array {
        return $this->postParams;
    }
    
    /**
     * Get a request header.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function header(string $key, $default = null) {
        return $this->headers[$key] ?? $default;
    }

    /**
     * Get a server parameter.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function server(string $key, $default = null) {
        return $this->serverParams[$key] ?? $default;
    }

    /**
     * Set the authenticated user on the request.
     *
     * @param mixed $user
     * @return $this
     */
    public function setUser($user): self {
        $this->user = $user;
        return $this;
    }

    /**
     * Get the authenticated user.
     *
     * @return mixed
     */
    public function user() {
        return $this->user;
    }
}
