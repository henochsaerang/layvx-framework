<?php

namespace App\Commands;

use App\Core\Command;

class DeployCpanelCommand extends Command
{
    protected $signature = 'deploy:cpanel';
    protected $description = 'Build aplikasi untuk deployment ke cPanel (Struktur Terpisah Aman).';

    public function handle()
    {
        $this->info('Memulai proses build untuk cPanel...');

        $buildDir = 'build_cpanel';
        $rootHomeDir = $buildDir . '/1_upload_ke_ROOT_HOME';
        $publicHtmlDir = $buildDir . '/2_upload_ke_PUBLIC_HTML';

        // 1. Persiapan folder build
        if (is_dir($buildDir)) {
            $this->deleteDirectory($buildDir);
            $this->info("Folder '{$buildDir}' lama telah dibersihkan.");
        }
        mkdir($buildDir);
        mkdir($rootHomeDir, 0755, true);
        mkdir($publicHtmlDir, 0755, true);
        $this->info("Folder build '{$buildDir}' dan sub-folder telah dibuat.");

        // 2. Copy folder inti
        $coreFolders = ['app', 'config', 'routes', 'views', 'layvx'];
        foreach ($coreFolders as $folder) {
            $this->recursiveCopy($folder, $rootHomeDir . '/' . $folder);
            $this->info("Folder '{$folder}' telah disalin ke '{$rootHomeDir}'.");
        }

        // Salin file .env
        if (file_exists('.env')) {
            copy('.env', $rootHomeDir . '/.env');
            $this->info("File '.env' telah disalin.");
        } else {
            $this->warning("File '.env' tidak ditemukan. Anda mungkin perlu membuat .env.example sebagai gantinya.");
        }

        // 3. Copy folder public
        $publicSource = 'public';
        $sourceItems = new \DirectoryIterator($publicSource);
        foreach ($sourceItems as $item) {
            if ($item->isDot()) continue;
            $sourcePath = $item->getPathname();
            $destinationPath = $publicHtmlDir . '/' . $item->getBasename();
            if ($item->isDir()) {
                $this->recursiveCopy($sourcePath, $destinationPath);
            } else {
                copy($sourcePath, $destinationPath);
            }
        }
        $this->info("Isi folder '{$publicSource}' telah disalin ke '{$publicHtmlDir}'.");

        // 4. Cek index.php (hanya konfirmasi)
        $indexPath = $publicHtmlDir . '/index.php';
        if (file_exists($indexPath)) {
            $indexContent = file_get_contents($indexPath);
            if (strpos($indexContent, "'/../app/Core/autoloader.php'") !== false) {
                $this->info("Path di '{$indexPath}' sudah benar (../app). Tidak ada perubahan diperlukan.");
            } else {
                $this->warning("Path di '{$indexPath}' mungkin perlu diperiksa manual. Pastikan me-load '../app/Core/autoloader.php'.");
            }
        }

        // 5. Buat file instruksi
        $instructions = <<<EOT
Selamat! Proses build untuk cPanel telah selesai.

Ikuti langkah-langkah deployment berikut:

1.  **UPLOAD KE ROOT_HOME:**
    Upload SELURUH ISI dari folder '1_upload_ke_ROOT_HOME' ke direktori root hosting Anda.
    Ini adalah direktori paling luar, yang biasanya sejajar dengan folder 'public_html'.
    Contoh: /home/username/

2.  **UPLOAD KE PUBLIC_HTML:**
    Upload SELURUH ISI dari folder '2_upload_ke_PUBLIC_HTML' ke dalam folder 'public_html' di hosting Anda.
    Timpa (overwrite) file yang ada jika ditanya.

3.  **KONFIGURASI DATABASE:**
    Setelah file di-upload, buka file manager di cPanel Anda.
    Navigasi ke direktori root (langkah 1), cari dan edit file '.env'.
    Sesuaikan variabel berikut dengan detail database cPanel Anda:
    - DB_HOST
    - DB_PORT
    - DB_DATABASE
    - DB_USERNAME
    - DB_PASSWORD

Deployment Anda sekarang seharusnya sudah live.
EOT;
        file_put_contents($buildDir . '/BACA_SAYA.txt', $instructions);
        $this->info("File instruksi 'BACA_SAYA.txt' telah dibuat.");

        $this->success("Build untuk cPanel berhasil dibuat di folder '{$buildDir}'.");
    }

    private function recursiveCopy($source, $destination)
    {
        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $destPath = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if ($item->isDir()) {
                if (!file_exists($destPath)) {
                    mkdir($destPath);
                }
            } else {
                copy($item, $destPath);
            }
        }
    }

    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return rmdir($dir);
    }
}
