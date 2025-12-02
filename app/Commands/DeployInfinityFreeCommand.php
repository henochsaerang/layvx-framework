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
        $this->info('Starting InfinityFree deployment build...');

        $rootDir = dirname(__DIR__, 2);
        $buildDir = $rootDir . '/build_infinityfree';
        $htdocsDir = $buildDir . '/htdocs';

        // 1. Clean up previous build
        if (is_dir($buildDir)) {
            $this->info("Cleaning up old build directory...");
            $this->deleteDirectory($buildDir);
        }

        // 2. Create build directories
        $this->info("Creating new build directory structure...");
        mkdir($buildDir, 0777, true);
        mkdir($htdocsDir, 0777, true);

        // 3. Copy required application folders
        $foldersToCopy = ['app', 'config', 'routes', 'views', 'layvx'];
        foreach ($foldersToCopy as $folder) {
            $this->info("Copying '{$folder}' folder...");
            $this->copyDirectory($rootDir . '/' . $folder, $htdocsDir . '/' . $folder);
        }

        // 4. Copy contents of the public folder
        $this->info("Copying contents of 'public' folder...");
        $this->copyDirectoryContents($rootDir . '/public', $htdocsDir);
        
        // Ensure the uploads directory exists
        if (!is_dir($htdocsDir . '/uploads')) {
            mkdir($htdocsDir . '/uploads', 0777, true);
        }

        // 5. Patch index.php for the new structure
        $this->info("Patching 'index.php' for shared hosting...");
        $this->patchIndexPhp($htdocsDir . '/index.php');

        // 6. Create .htaccess for security and routing
        $this->info("Creating '.htaccess' file...");
        $this->createHtaccess($htdocsDir);

        // 7. Create .env template
        $this->info("Creating '.env' template file...");
        $this->createEnvTemplate($htdocsDir);

        // 8. Create instruction file
        $this->info("Creating instruction file 'BACA_SAYA.txt'...");
        $this->createInstructionFile($buildDir);

        $this->info("\nBuild complete!");
        $this->info("All files are ready in the 'build_infinityfree' directory.", 'green');
        $this->info("Please follow the instructions in 'build_infinityfree/BACA_SAYA.txt' to deploy.", 'yellow');
    }

    private function patchIndexPhp(string $filePath)
    {
        if (!file_exists($filePath)) {
            $this->error("Could not find index.php to patch.");
            return;
        }

        $content = file_get_contents($filePath);

        // Replace paths
        $content = str_replace(
            "require_once '../app/Core/Env.php';",
            "require_once __DIR__ . '/app/Core/Env.php';",
            $content
        );
        $content = str_replace(
            "\App\Core\Env::load(__DIR__ . '/..');",
            "\App\Core\Env::load(__DIR__);",
            $content
        );
        $content = str_replace(
            "require_once '../app/Core/autoloader.php';",
            "require_once __DIR__ . '/app/Core/autoloader.php';",
            $content
        );
         $content = str_replace(
            "require_once '../routes/web.php';",
            "require_once __DIR__ . '/routes/web.php';",
            $content
        );

        file_put_contents($filePath, $content);
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
   - Gunakan tab 'Import' untuk mengupload file SQL dari database lokal Anda.

6. Selesai!
   - Kunjungi website Anda untuk melihat apakah sudah berjalan. Jika ada error '500', kemungkinan besar ada kesalahan pada konfigurasi '.env'.

Terima kasih telah menggunakan LayVX Framework!
TXT;

        file_put_contents($buildDir . '/BACA_SAYA.txt', $content);
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
