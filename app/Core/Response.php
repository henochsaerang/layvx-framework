<?php

namespace App\Core;

class Response {
    protected $content;
    protected $statusCode;
    protected $headers = [];

    public function __construct($content = '', int $statusCode = 200, array $headers = []) {
        $this->content = $content;
        $this->statusCode = $statusCode;
        foreach ($headers as $key => $value) {
            $this->header($key, $value);
        }
    }

    /**
     * Set a header on the response.
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function header(string $key, string $value): self {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Sends HTTP headers and content.
     */
    public function send() {
        // Send status code
        http_response_code($this->statusCode);

        // Send headers
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }

        // Send content
        echo $this->content;
    }

    /**
     * Create a new JSON response.
     *
     * @param mixed $data
     * @param int $statusCode
     * @param array $headers
     * @return static
     */
    public static function json($data, int $statusCode = 200, array $headers = []): self {
        $headers['Content-Type'] = 'application/json';
        $content = json_encode($data);
        return new static($content, $statusCode, $headers);
    }

    /**
     * Create a new redirect response.
     *
     * @param string $url
     * @param int $statusCode
     * @return static
     */
    public static function redirect(string $url, int $statusCode = 302): self {
        return new static('', $statusCode, ['Location' => $url]);
    }

    /**
     * Create a new view response.
     *
     * @param string $view
     * @param array $data
     * @param int $statusCode
     * @return static
     */
    public static function view(string $view, array $data = [], int $statusCode = 200): self {
        // This is a bit of a trick. We capture the output of the render function.
        ob_start();
        render($view, $data);
        $content = ob_get_clean();
        
        return new static($content, $statusCode, ['Content-Type' => 'text/html']);
    }
}
