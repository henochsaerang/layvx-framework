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

    public function handle()
    {
        $rootDir = dirname(__DIR__, 2);
        $buildDir = $rootDir . '/build_desktop';

        if (!is_dir($buildDir)) {
            $this->info('Tidak ditemukan folder build desktop.');
            return;
        }

        $this->info("Menghapus folder '{$buildDir}'...");
        $this->deleteDirectory($buildDir);
        $this->info("\033[32mBuild desktop berhasil dihapus. Aplikasi kembali bersih.\033[0m");
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
