<?php

namespace App\Commands;

use App\Core\Command;

class MakeViewCommand extends Command {
    protected $signature = 'buat:view';
    protected $description = 'Membuat file View baru di direktori views/. Gunakan notasi titik (dot notation) untuk sub-direktori.';

    public function handle(array $args = []) {
        $viewName = $args[0] ?? null;
        if (empty($viewName)) {
            echo "Error: Please provide a name for the view.\n";
            echo "Usage: layvx buat:view <view.name>\n";
            exit(1);
        }
        
        $basePath = __DIR__ . '/../../';
        
        // Convert dot notation (e.g., auth.login) to file path (views/auth/login.php)
        $relativePath = str_replace('.', '/', $viewName);
        $directory = dirname($basePath . 'views/' . $relativePath);
        $filePath = $basePath . 'views/' . $relativePath . '.php';

        // Check and create directory if needed (e.g., views/auth)
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                echo "Error: Failed to create view directory: {$directory}\n";
                return;
            }
            echo "Directory created: " . str_replace($basePath, '', $directory) . "\n";
        }

        if (file_exists($filePath)) {
            echo "Error: View file {$filePath} already exists.\n";
            exit(1);
        }

        $stub = <<<PHP
Berikan di sini konten awal untuk view Anda.
PHP;
        
        // Jangan tambahkan tag PHP pembuka agar editor tidak mendeteksi syntax error pada @directive
        $finalContent = $stub;


        if (file_put_contents($filePath, $finalContent) === false) {
            echo "Error: Could not create view file.\n";
            exit(1);
        }
        echo "View created successfully: views/{$relativePath}.php\n";
        
        // Tambahkan saran untuk membuat layout.app jika belum ada
        $layoutPath = $basePath . 'views/layouts/app.php';
        if (!file_exists($layoutPath)) {
             echo "\nNote: Layout 'views/layouts/app.php' not found. Create it using HTML structure with @yield('content') to use this view's layout inheritance.\n";
        }
    }
}