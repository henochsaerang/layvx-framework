<?php

namespace App\Core;

class Request {
    protected $uri;
    protected $method;
    protected $queryParams = [];
    protected $postParams = [];
    protected $headers = [];
    protected $serverParams = [];

    public function __construct() {
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->queryParams = $_GET;
        $this->postParams = $_POST;
        $this->headers = getallheaders();
        $this->serverParams = $_SERVER;

        // Handle JSON input
        if (strpos($this->header('Content-Type', ''), 'application/json') !== false) {
            $jsonData = json_decode(file_get_contents('php://input'), true);
            $this->postParams = array_merge($this->postParams, $jsonData ?: []);
        }
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
}
