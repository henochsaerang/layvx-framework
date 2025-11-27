<?php

namespace App\Commands;

use App\Core\Command;

class ServeCommand extends Command {
    protected $signature = 'serve';
    protected $description = 'Serve the application on the PHP development server';

    public function handle(array $args = []) {
        $host = '127.0.0.1';
        $port = 8000;
        $basePath = __DIR__ . '/../../';
        $documentRoot = $basePath . 'public';

        // Check for custom port in arguments
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--port=')) {
                $port = (int) substr($arg, 7);
                break;
            }
        }
        
        // --- Document Root Directory Setup ---
        if (!is_dir($documentRoot)) {
            if (!mkdir($documentRoot, 0755, true)) {
                echo "Error: Failed to find or create document root directory: {$documentRoot}\n";
                exit(1);
            }
            echo "Warning: Document root directory was missing and created: {$documentRoot}\n";
            echo "Please ensure the index.php file is placed inside this directory to run the application.\n";
        }

        // --- Formatted Output ---
        $url = "http://{$host}:{$port}";
        $lineLength = max(strlen($url) + 6, 30); // Minimal 30 karakter, atau menyesuaikan panjang URL
        $separator = str_repeat(' ', $lineLength);
        
        echo "\n";
        echo "Starting LayVX (By Henoch A Saerang) development server...\n";
        echo "\n";
        echo "Server running on:\n";
        echo "\n";
        
        // Displaying the URL with padding to make it stand out
        echo "                 [ {$url} ]\n";
        
        echo "\n";
        echo "Press Ctrl+C to stop the server.\n";
        // Informasi Document root tidak ditampilkan kecuali di-debug
        // echo "Document root: {$documentRoot}\n";
        
        // Run the PHP built-in server
        passthru("php -S {$host}:{$port} -t \"{$documentRoot}\"");
    }
}