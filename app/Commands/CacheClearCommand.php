<?php

namespace App\Commands;

use App\Core\Command;

class CacheClearCommand extends Command {
    protected $signature = 'cache:clear';
    protected $description = 'Menghapus semua file cache view.';

    public function handle(array $args = []) {
        $cache_path = __DIR__ . '/../../storage/framework/views';
        if (!is_dir($cache_path)) {
            echo "Cache directory not found: {$cache_path}\n";
            exit(1);
        }

        $files = glob($cache_path . '/*'); // Get all file names
        $cleared_count = 0;
        foreach($files as $file){ // iterate files
            if(is_file($file)) {
                if (unlink($file)) { // delete file
                    $cleared_count++;
                } else {
                    echo "Failed to delete: {$file}\n";
                }
            }
        }
        echo "Cleared {$cleared_count} view cache files.\n";
    }
}
