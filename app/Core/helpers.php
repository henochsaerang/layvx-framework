<?php
// app/Core/helpers.php

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
     * Compile and render a view using the ViewCompiler.
     *
     * @param string $view The name of the view (e.g., 'auth.login').
     * @param array $data The data to be extracted into variables for the view.
     */
    function render(string $view, array $data = []) {
        $basePath = __DIR__ . '/../../';
        $viewPath = $basePath . 'views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new Exception("View not found: {$viewPath}");
        }

        $compiledDir = $basePath . 'storage/framework/views/';
        if (!is_dir($compiledDir)) {
            mkdir($compiledDir, 0755, true);
        }
        $compiledPath = $compiledDir . sha1($viewPath) . '.php';

        // Instantiate the compiler
        $compiler = new \App\Core\ViewCompiler();
        
        // Compile the view file
        $compiledContent = $compiler->compile($viewPath);

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

// --- App Container Helper ---
if (!function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param string|null $abstract
     * @return mixed|\App\Core\Container
     */
    function app($abstract = null) {
        $container = \App\Core\App::getContainer();
        if (is_null($abstract)) {
            return $container;
        }
        return $container->resolve($abstract);
    }
}