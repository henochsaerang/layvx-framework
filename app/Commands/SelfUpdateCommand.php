<?php

namespace App\Commands;

use App\Core\Command;
use ZipArchive;

class SelfUpdateCommand extends Command
{
    protected $signature = 'self:update';
    protected $description = 'Memperbarui Core Framework ke versi terbaru dari GitHub.';

    private const REPO_URL = 'https://github.com/henochsaerang/layvx-framework/archive/refs/heads/main.zip';

    public function handle()
    {
        $basePath = realpath(__DIR__ . '/../../..');
        $zipPath = $basePath . '/storage/framework/update.zip';
        $extractPath = $basePath . '/storage/framework/update_temp';

        // 1. Download
        $this->info("Mengunduh pembaruan dari GitHub...");
        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }
        $downloaded = @file_put_contents($zipPath, file_get_contents(self::REPO_URL));
        if ($downloaded === false) {
            $this->error("Gagal mengunduh pembaruan. Periksa koneksi internet Anda.");
            return;
        }
        $this->info("Unduhan selesai.");

        // 2. Extract
        $this->info("Mengekstrak file pembaruan...");
        $zip = new ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            if (is_dir($extractPath)) {
                $this->deleteDirectory($extractPath);
            }
            mkdir($extractPath, 0755, true);
            $zip->extractTo($extractPath);
            $zip->close();
            $this->info("Ekstraksi selesai.");
        } else {
            $this->error("Gagal mengekstrak file zip.");
            @unlink($zipPath);
            return;
        }

        // 3. Identify extracted folder name
        $extractedItems = array_diff(scandir($extractPath), ['.', '..']);
        if (count($extractedItems) !== 1) {
            $this->error("Struktur arsip tidak terduga.");
            $this->cleanup($zipPath, $extractPath);
            return;
        }
        $sourceDirName = reset($extractedItems);
        $sourcePath = $extractPath . '/' . $sourceDirName;

        // 4. Surgical Replacement
        $this->info("Memulai proses pembaruan file core...");

        $targets = [
            'app/Core',
            'app/Commands',
            'layvx/layvx',
        ];

        try {
            foreach ($targets as $target) {
                $source = $sourcePath . '/' . $target;
                $destination = $basePath . '/' . $target;

                if (!file_exists($source)) {
                     $this->warning("Peringatan: source '{$target}' tidak ditemukan dalam pembaruan, dilewati.");
                     continue;
                }

                $this->info("Memperbarui '{$target}'...");

                if (is_dir($destination)) {
                    $this->deleteDirectory($destination);
                } elseif (file_exists($destination)) {
                    unlink($destination);
                }
                
                if (is_dir($source)) {
                    $this->copyDirectory($source, $destination);
                } else {
                    copy($source, $destination);
                }
            }
        } catch (\Exception $e) {
            $this->error("Terjadi kesalahan saat mengganti file: " . $e->getMessage());
            $this->cleanup($zipPath, $extractPath);
            return;
        }

        $this->info("Pembaruan file core selesai.");

        // 5. Cleanup
        $this->info("Membersihkan file sementara...");
        $this->cleanup($zipPath, $extractPath);

        $this->info("----------------------------------------------------------");
        $this->info("Framework berhasil diperbarui ke versi terbaru!");
        $this->info("Silakan jalankan 'layvx migrate' jika ada perubahan database.");
        $this->info("----------------------------------------------------------");
    }

    private function cleanup(string $zipPath, string $extractPath)
    {
        @unlink($zipPath);
        if (is_dir($extractPath)) {
            $this->deleteDirectory($extractPath);
        }
    }

    private function deleteDirectory(string $dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function copyDirectory(string $source, string $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0775, true);
        }
        $items = array_diff(scandir($source), ['.', '..']);
        foreach ($items as $item) {
            $sourcePath = $source . DIRECTORY_SEPARATOR . $item;
            $destPath = $dest . DIRECTORY_SEPARATOR . $item;
            if (is_dir($sourcePath)) {
                $this->copyDirectory($sourcePath, $destPath);
            } else {
                copy($sourcePath, $destPath);
            }
        }
    }
}
