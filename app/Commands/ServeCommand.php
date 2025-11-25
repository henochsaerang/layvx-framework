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

        echo "Starting LayVX (By Henoch A Saerang) development server...\n";
        echo "Server running on: http://{$host}:{$port}\n";
        echo "Press Ctrl+C to stop the server.\n";
        passthru("php -S {$host}:{$port} -t \"{$documentRoot}\"");
    }
}
