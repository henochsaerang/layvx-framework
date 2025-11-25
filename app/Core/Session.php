<?php

namespace App\Core;

class Session
{
    /**
     * Start the session if not already started.
     */
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Set a value in the session.
     *
     * @param string $key
     * @param mixed $value
     */
    public static function set(string $key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a value from the session.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if a key exists in the session.
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a key from the session.
     *
     * @param string $key
     */
    public static function forget(string $key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * Get the CSRF token from the session.
     *
     * @return string|null
     */
    public static function token(): ?string
    {
        return self::get('tuama_token');
    }

    /**
     * Regenerate and get a new CSRF token.
     *
     * @return string
     */
    public static function regenerateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        self::set('tuama_token', $token);
        return $token;
    }
}
