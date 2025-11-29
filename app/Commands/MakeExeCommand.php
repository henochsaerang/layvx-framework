<?php

namespace App\Commands;

use App\Core\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

class MakeExeCommand extends Command
{
    protected $signature = 'buat:exe';
    protected $description = 'Membangun aplikasi menjadi paket portable (Desktop App)';

    public function handle()
    {
        $this->info('Memulai proses build aplikasi desktop...');

        $rootDir = dirname(__DIR__, 2);
        $buildDir = $rootDir . '/build_desktop';

        // 1. Buat atau bersihkan folder build
        if (is_dir($buildDir)) {
            $this->info("Folder 'build_desktop' sudah ada. Membersihkan folder...");
            $this->deleteDirectory($buildDir);
        }
        mkdir($buildDir, 0755, true);

        // 2. Salin folder dan file yang diperlukan
        $itemsToCopy = ['app', 'config', 'public', 'routes', 'views'];
        foreach ($itemsToCopy as $item) {
            $source = $rootDir . '/' . $item;
            $destination = $buildDir . '/' . $item;
            if (is_dir($source)) {
                $this->info("Menyalin folder '{$item}'...");
                $this->recursiveCopy($source, $destination);
            }
        }
        
        // Salin file .env
        $envFile = $rootDir . '/.env';
        if (file_exists($envFile)) {
            $this->info("Menyalin file '.env'...");
            copy($envFile, $buildDir . '/.env');
        }

        // 3. Buat launcher script
        $this->info('Membuat launcher script (LayVX_App.bat)...');
        $batchContent = $this->getBatchScriptContent();
        file_put_contents($buildDir . '/LayVX_App.bat', $batchContent);

        // 4. Beri output ke user
        $this->info('');
        $this->info("\033[32mBuild selesai di folder 'build_desktop'.\033[0m");
        $this->info('Untuk membuatnya 100% portable, salin folder instalasi PHP Anda ke dalam direktori tersebut.');
    }

    private function getBatchScriptContent(): string
    {
        return <<<BATCH
@echo off
echo Memulai LayVX Desktop...
echo.

:: 1. Start PHP Server (Background/Minimized)
:: Pastikan 'php' ada di PATH sistem atau salin folder 'php' ke direktori ini.
start "LayVX Server" /B php -S 127.0.0.1:8888 -t public

:: 2. Tunggu sebentar agar server siap
echo Menunggu server siap...
timeout /t 2 /nobreak >nul

:: 3. Buka UI (Edge Mode App)
echo Membuka aplikasi...
start msedge --app=http://127.0.0.1:8888 --user-data-dir="%TEMP%\LayVXProfile"

BATCH;
    }

    private function recursiveCopy(string $source, string $destination)
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $destPath = $destination . '/' . $iterator->getSubPathName();
            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath);
                }
            } else {
                copy($item, $destPath);
            }
        }
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
