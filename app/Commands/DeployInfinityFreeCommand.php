<?php

namespace App\Commands;

use App\Core\Command;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;

class DeployInfinityFreeCommand extends Command
{
    protected $signature = 'deploy:infinityfree';
    protected $description = 'Build the application for InfinityFree shared hosting deployment.';

    public function handle(array $args = [])
    {
        echo 'Starting InfinityFree deployment build...' . "\n";

        $rootDir = dirname(__DIR__, 2);
        $buildDir = $rootDir . '/build_infinityfree';
        $htdocsDir = $buildDir . '/htdocs';

        // 1. Clean up previous build
        if (is_dir($buildDir)) {
            echo "Cleaning up old build directory..." . "\n";
            $this->deleteDirectory($buildDir);
        }

        // 2. Create build directories
        echo "Creating new build directory structure..." . "\n";
        mkdir($buildDir, 0777, true);
        mkdir($htdocsDir, 0777, true);

        // 3. Copy required application folders
        $foldersToCopy = ['app', 'config', 'routes', 'views', 'layvx'];
        foreach ($foldersToCopy as $folder) {
            echo "Copying '{$folder}' folder..." . "\n";
            $this->copyDirectory($rootDir . '/' . $folder, $htdocsDir . '/' . $folder);
        }

        // 4. Copy contents of the public folder
        echo "Copying contents of 'public' folder..." . "\n";
        $this->copyDirectoryContents($rootDir . '/public', $htdocsDir);
        
        // Ensure the uploads directory exists
        if (!is_dir($htdocsDir . '/uploads')) {
            mkdir($htdocsDir . '/uploads', 0777, true);
        }

        // 5. Patch index.php for the new structure
        echo "Patching 'index.php' for shared hosting..." . "\n";
        $this->patchIndexPhp($htdocsDir . '/index.php');

        // 6. Create .htaccess for security and routing
        echo "Creating '.htaccess' file..." . "\n";
        $this->createHtaccess($htdocsDir);

        // 7. Create .env template
        echo "Creating '.env' template file..." . "\n";
        $this->createEnvTemplate($htdocsDir);

        // 8. Dump database (Pindahkan ke atas agar file SQL masuk ke build folder sebelum instruksi dibuat jika ingin disertakan infonya, tapi urutan ini juga OK)
        $this->dumpDatabase($buildDir);

        // 9. Create instruction file
        echo "Creating instruction file 'BACA_SAYA.txt'..." . "\n";
        $this->createInstructionFile($buildDir);

        echo "\n" . 'Build complete!' . "\n";
        echo "\033[32mAll files are ready in the 'build_infinityfree' directory.\033[0m" . "\n";
        echo "\033[33mPlease follow the instructions in 'build_infinityfree/BACA_SAYA.txt' to deploy.\033[0m" . "\n";
    }

    private function patchIndexPhp(string $filePath)
    {
        if (!file_exists($filePath)) {
            echo "Error: Could not find index.php to patch." . "\n";
            return;
        }

        $content = file_get_contents($filePath);
        $originalContent = $content;

        echo "--- Starting aggressive patching of index.php ---" . "\n";

        // Define replacements as per user request
        // Urutan penting: string yang lebih panjang/spesifik sebaiknya di atas
        $replacements = [
            "\App\Core\Env::load(__DIR__ . '/..');" => "\App\Core\Env::load(__DIR__);",
            "../app" => "app",
            "../config" => "config",
            "../routes" => "routes",
        ];

        foreach ($replacements as $old => $new) {
            $content = str_replace($old, $new, $content, $count);
            if ($count > 0) {
                echo "[OK] Replaced '{$old}' with '{$new}' ({$count} occurrences).\n";
            }
        }

        if ($originalContent !== $content) {
            file_put_contents($filePath, $content);
            echo "--- Finished patching index.php. File was modified and saved. ---" . "\n";
        } else {
            echo "--- No changes were needed for index.php. ---" . "\n";
        }
    }

    private function createHtaccess(string $htdocsDir)
    {
        $content = <<<HTACCESS
# Block access to sensitive directories
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(app|config|routes|views|layvx|\.env)(\$|/.*) - [F,L]
</IfModule>

# Redirect all other requests to index.php
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
</IfModule>

# General security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Disable directory listing
Options -Indexes
HTACCESS;

        file_put_contents($htdocsDir . '/.htaccess', $content);
    }

    private function createEnvTemplate(string $htdocsDir)
    {
        $content = <<<ENV
# InfinityFree Environment Configuration
# Fill this file with your database credentials from the control panel.

APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=sqlXXX.epizy.com
DB_PORT=3306
DB_DATABASE=epiz_XXXXXXXX_dbname
DB_USERNAME=epiz_XXXXXXXX
DB_PASSWORD=YourPassword

SESSION_DRIVER=file
SESSION_LIFETIME=120
ENV;

        file_put_contents($htdocsDir . '/.env', $content);
    }

    private function createInstructionFile(string $buildDir)
    {
        $content = <<<TXT
=====================================
 INSTRUKSI DEPLOYMENT - INFINITYFREE
=====================================

Ikuti langkah-langkah berikut untuk men-deploy aplikasi Anda ke InfinityFree:

1. Buka File Manager di Control Panel InfinityFree Anda.
2. Navigasi ke dalam folder 'htdocs'. PENTING: Jangan hapus folder ini.
3. Upload SEMUA ISI DARI FOLDER 'htdocs' yang ada di dalam direktori build ini ke dalam folder 'htdocs' di hosting Anda.
   - Anda akan mengupload folder seperti 'app', 'config', 'views', dan file seperti 'index.php', '.htaccess'.
   - Cara terbaik adalah dengan membuat file ZIP dari isi folder 'htdocs' ini, menguploadnya, lalu mengekstraknya di hosting.

4. Edit File '.env' di Hosting:
   - Setelah semua file diupload, cari dan edit file '.env'.
   - Ganti placeholder (seperti sqlXXX.epizy.com, epiz_XXXXXXXX, dll.) dengan informasi database Anda yang sebenarnya. Anda bisa menemukan informasi ini di 'MySQL Databases' pada Control Panel InfinityFree.

5. Import Database (Jika Diperlukan):
   - Buka 'phpMyAdmin' dari Control Panel.
   - Pilih database Anda.
   - Gunakan tab 'Import' untuk mengupload file SQL dari database lokal Anda yang juga sudah ada di folder build ini (berekstensi .sql).

6. Selesai!
   - Kunjungi website Anda untuk melihat apakah sudah berjalan. Jika ada error '500', kemungkinan besar ada kesalahan pada konfigurasi '.env'.

7. CATATAN PENTING - UPLOAD GAMBAR/FILE:
   Jika aplikasi Anda memiliki fitur upload (di Controller), path tujuan upload MUNGKIN perlu disesuaikan.
   
   - SALAH (Path Lama/Local): 
     \$targetDir = "../public/uploads/";
     // atau 
     \$targetDir = __DIR__ . '/../../public/uploads/';
   
   - BENAR (Path Hosting): 
     Gunakan 'DOCUMENT_ROOT' agar selalu mengarah ke folder htdocs yang benar.
     \$targetDir = \$_SERVER['DOCUMENT_ROOT'] . '/uploads/';
   
   Hal ini karena di struktur InfinityFree ini, isi folder 'public' telah dikeluarkan ke root 'htdocs', sehingga folder 'public' secara fisik tidak ada lagi di path tersebut.

Terima kasih telah menggunakan LayVX Framework!
TXT;

        file_put_contents($buildDir . '/BACA_SAYA.txt', $content);
    }

    private function dumpDatabase(string $buildDir)
    {
        echo "Dumping database..." . "\n";

        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $user = $_ENV['DB_USERNAME'] ?? 'root';
        $pass = $_ENV['DB_PASSWORD'] ?? '';
        $dbName = $_ENV['DB_DATABASE'] ?? null;

        if (!$dbName) {
            echo "\033[31mError: DB_DATABASE not set in .env. Skipping database dump.\033[0m" . "\n";
            return;
        }

        $outputPath = $buildDir . '/' . $dbName . '.sql';

        // Use MYSQL_PWD to avoid password on command line
        putenv('MYSQL_PWD=' . $pass);

        $command = sprintf(
            'mysqldump -h %s -u %s %s > %s',
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($dbName),
            escapeshellarg($outputPath)
        );

        exec($command, $output, $return_var);

        // Unset the environment variable
        putenv('MYSQL_PWD=');

        if ($return_var !== 0) {
            echo "\033[31mError: Failed to dump database. Is 'mysqldump' in your system's PATH?\033[0m" . "\n";
            echo "\033[33mNote: If you are on Windows, add your MySQL bin directory (e.g., C:\xampp\mysql\bin) to your System PATH environment variable.\033[0m" . "\n";
        } else {
            echo "\033[32mDatabase dumped successfully to '{$outputPath}'.\033[0m" . "\n";
        }
    }

    private function copyDirectory(string $source, string $destination)
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $destPath = $destination . '/' . $iterator->getSubPathName();
            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0777, true);
                }
            } else {
                copy($item, $destPath);
            }
        }
    }
    
    private function copyDirectoryContents(string $source, string $destination)
    {
        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file !== '.' && $file !== '..') {
                $sourcePath = $source . '/' . $file;
                $destPath = $destination . '/' . $file;
                if (is_dir($sourcePath)) {
                    $this->copyDirectory($sourcePath, $destPath);
                } else {
                    copy($sourcePath, $destPath);
                }
            }
        }
        closedir($dir);
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
                @rmdir($file->getRealPath());
            } else {
                @unlink($file->getRealPath());
            }
        }

        @rmdir($dir);
    }
}