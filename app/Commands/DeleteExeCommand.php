<?php

namespace App\Commands;

use App\Core\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

class DeleteExeCommand extends Command
{
    protected $signature = 'buat:hapus_exe';
    protected $description = 'Menghapus folder build aplikasi desktop (build_desktop).';

    public function handle(array $args = [])
    {
        $rootDir = dirname(__DIR__, 2);
        $buildDir = $rootDir . '/build_desktop';

        if (!is_dir($buildDir)) {
            echo 'Tidak ditemukan folder build desktop.' . "\n";
            return;
        }

        echo "Menghapus folder '{$buildDir}'..." . "\n";
        $this->deleteDirectory($buildDir);
        echo "\033[32mBuild desktop berhasil dihapus. Aplikasi kembali bersih.\033[0m" . "\n";
    }

    private function deleteDirectory(string $dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($dir);
    }
}
