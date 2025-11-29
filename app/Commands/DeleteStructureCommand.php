<?php

namespace App\Commands;

use App\Core\Command;
use App\Core\StructureDefinitions;

class DeleteStructureCommand extends Command
{
    protected $description = 'Delete a project structure (mvc, adr, minimal).';

    public function handle(array $args = [])
    {
        $commandName = $args['command_name'] ?? '';
        $parts = explode(':', $commandName);
        $commandPart = $parts[1] ?? '';
        $typeParts = explode('_', $commandPart);
        $type = end($typeParts);

        if (!$type) {
            echo "Error: Tipe struktur tidak valid. Contoh: buat:hapus_mvc\n";
            return;
        }

        echo "Menghapus struktur " . strtoupper($type) . "...\n";

        $directories = StructureDefinitions::getDirectories($type);

        if (empty($directories)) {
            echo "Error: Tipe preset '{$type}' tidak dikenal.\n";
            return;
        }

        // Hapus file boilerplate dulu
        $filesToDelete = ['public/index.php', 'routes/web.php', 'app/Providers/AppServiceProvider.php'];
        foreach ($filesToDelete as $file) {
            if (file_exists($file)) {
                unlink($file);
                echo "File dihapus: {$file}\n";
            }
        }
        
        // Hapus direktori secara rekursif, urutan dibalik untuk keamanan
        $directories = array_reverse($directories);
        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $this->deleteDirectory($dir);
                echo "Direktori dihapus: {$dir}\n";
            }
        }

        echo "\nStruktur " . strtoupper($type) . " berhasil dihapus.\n";
    }

    /**
     * Recursively delete a directory.
     *
     * @param string $dir
     * @return bool
     */
    private function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $items = array_diff(scandir($dir), ['.', '..']);

        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($dir);
    }
}
