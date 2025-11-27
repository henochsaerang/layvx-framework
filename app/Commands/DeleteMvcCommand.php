<?php

namespace App\Commands;

use App\Core\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class DeleteMvcCommand extends Command {
    protected $signature = 'buat:hapus_mvc';
    protected $description = 'Menghapus semua direktori struktur MVC (Controllers, Models, Routes, Views, Database, Public).';

    public function handle(array $args = []) {
        $basePath = __DIR__ . '/../../';
        
        $directories = [
            'app/Controllers',
            'app/Models',
            'app/Middleware',
            'app/Providers',
            'database', // Hapus seluruh folder database
            'routes',
            'public',
            'views',
            'storage/framework', // Hapus cache views
        ];

        echo "Deleting LayVX MVC structure...\n";
        $deletedCount = 0;

        foreach ($directories as $dir) {
            $fullPath = $basePath . $dir;
            if (is_dir($fullPath)) {
                $this->deleteDirectory($fullPath);
                echo "DELETED: {$dir}\n";
                $deletedCount++;
            }
        }
        
        // Hapus file bootstrap utama di public dan routes
        @unlink($basePath . 'public/index.php');
        @unlink($basePath . 'public/.htaccess');
        @unlink($basePath . 'routes/web.php');

        echo "\nStructure deletion completed. {$deletedCount} main directories removed.\n";
    }
    
    /**
     * Helper untuk menghapus direktori secara rekursif.
     */
    private function deleteDirectory(string $dir) {
        if (!is_dir($dir)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                @rmdir($item->getRealPath());
            } else {
                @unlink($item->getRealPath());
            }
        }
        @rmdir($dir);
    }
}