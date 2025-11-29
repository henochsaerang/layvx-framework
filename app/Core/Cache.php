<?php

namespace App\Core;

use Closure;

class Cache
{
    /**
     * The base path for the cache files.
     * @var string
     */
    protected static $basePath = __DIR__ . '/../../storage/framework/cache/data/';

    /**
     * Get the full path to a cache file for a given key.
     * Ensures the cache directory exists.
     *
     * @param string $key
     * @return string
     */
    private static function getCacheFilePath(string $key): string
    {
        if (!is_dir(self::$basePath)) {
            mkdir(self::$basePath, 0755, true);
        }
        // Use sha1 for the filename to avoid issues with special characters in keys.
        return self::$basePath . sha1($key);
    }

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param string $key
     * @param mixed $value
     * @param int $seconds
     * @return bool
     */
    public static function put(string $key, $value, int $seconds): bool
    {
        $filePath = self::getCacheFilePath($key);
        $payload = json_encode([
            'expire' => time() + $seconds,
            'data' => $value,
        ]);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Cache Error: Failed to encode data for key ' . $key);
            return false;
        }

        return file_put_contents($filePath, $payload, LOCK_EX) !== false;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $filePath = self::getCacheFilePath($key);

        if (!file_exists($filePath)) {
            return $default;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return $default;
        }

        $payload = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($payload['expire'], $payload['data'])) {
            // Invalid cache file, clean it up.
            self::forget($key);
            return $default;
        }

        if (time() >= $payload['expire']) {
            // Cache has expired, clean it up.
            self::forget($key);
            return $default;
        }

        return $payload['data'];
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     * @return bool
     */
    public static function forget(string $key): bool
    {
        $filePath = self::getCacheFilePath($key);
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @param string $key
     * @param int $seconds
     * @param \Closure $callback
     * @return mixed
     */
    public static function remember(string $key, int $seconds, Closure $callback)
    {
        $value = self::get($key);

        if (!is_null($value)) {
            return $value;
        }

        $value = $callback();

        self::put($key, $value, $seconds);

        return $value;
    }
}
