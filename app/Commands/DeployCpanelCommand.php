<?php

namespace App\Commands;

use App\Core\App;
use App\Core\Command;
use PDO;

class DeployCpanelCommand extends Command
{
    protected $signature = 'deploy:cpanel';
    protected $description = 'Build aplikasi untuk deployment ke cPanel (Struktur Terpisah Aman).';

    private $pdo;

    public function handle(array $args = [])
    {
        echo "Memulai proses build untuk cPanel...\n";

        $buildDir = 'build_cpanel';
        $rootHomeDir = $buildDir . '/1_upload_ke_ROOT_HOME';
        $publicHtmlDir = $buildDir . '/2_upload_ke_PUBLIC_HTML';

        // 1. Persiapan folder build
        if (is_dir($buildDir)) {
            $this->deleteDirectory($buildDir);
            echo "Folder '{$buildDir}' lama telah dibersihkan.\n";
        }
        mkdir($buildDir);
        mkdir($rootHomeDir, 0755, true);
        mkdir($publicHtmlDir, 0755, true);
        echo "Folder build '{$buildDir}' dan sub-folder telah dibuat.\n";

        // 2. Copy folder inti & file
        $coreFolders = ['app', 'config', 'routes', 'views', 'layvx'];
        foreach ($coreFolders as $folder) {
            $this->recursiveCopy($folder, $rootHomeDir . '/' . $folder);
            echo "Folder '{$folder}' telah disalin ke '{$rootHomeDir}'.\n";
        }

        // Buat file .env baru khusus Produksi
        echo "Membuat file '.env' untuk produksi...\n";
        
        $envProductionContent = <<<'EOT'
# Environment Produksi (cPanel)
APP_ENV=production
APP_DEBUG=false
APP_URL=https://namadomain-anda.com

# Database Connection
# Silakan sesuaikan dengan detail dari menu "MySQL Databases" di cPanel
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=usernamecpanel_namadb
DB_USERNAME=usernamecpanel_user
DB_PASSWORD=password_db_anda

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120
EOT;

        file_put_contents($rootHomeDir . '/.env', $envProductionContent);
        echo "File '.env' produksi berhasil dibuat.\n";

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
        echo "Isi folder '{$publicSource}' telah disalin ke '{$publicHtmlDir}'.\n";
        
        // 4. Dump Database
        echo "Mencoba mengekspor database...\n";
        try {
            $this->pdo = App::getContainer()->resolve(PDO::class);
            $dbConfig = config('database');
            $dbName = $dbConfig['database'];
            $sqlFilePath = $rootHomeDir . '/database.sql';

            $handle = fopen($sqlFilePath, 'w');
            if ($handle === false) throw new \Exception("Tidak dapat membuat file SQL.");

            fwrite($handle, "-- LayVX SQL Dump for cPanel Deployment\n");
            fwrite($handle, "-- Database: {$dbName}\n");
            fwrite($handle, "-- Waktu: " . date('Y-m-d H:i:s') . "\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            $tables = $this->getTables();
            foreach ($tables as $table) {
                $this->dumpTableStructure($handle, $table);
                $this->dumpTableData($handle, $table);
            }
            
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($handle);
            echo "\033[32mDatabase berhasil diekspor ke '{$sqlFilePath}'.\033[0m\n";

        } catch (\Exception $e) {
            echo "\033[33mWarning:\033[0m Gagal mengekspor database. Error: " . $e->getMessage() . "\n";
            $noDbFile = $buildDir . '/no_database_found.txt';
            file_put_contents($noDbFile, "Proses ekspor database gagal pada " . date('Y-m-d H:i:s') . ".\nError: " . $e->getMessage());
            echo "File penanda '{$noDbFile}' telah dibuat.\n";
        }


        // 5. Cek index.php (hanya konfirmasi)
        $indexPath = $publicHtmlDir . '/index.php';
        if (file_exists($indexPath)) {
            $indexContent = file_get_contents($indexPath);
            if (strpos($indexContent, "../app/Core/autoloader.php") !== false) {
                echo "Path di '{$indexPath}' sudah benar (../app). Tidak ada perubahan diperlukan.\n";
            } else {
                echo "\033[33mWarning:\033[0m Path di '{$indexPath}' mungkin perlu diperiksa manual.\n";
            }
        }

        // 6. Buat file instruksi
        $this->createReadme($buildDir);
        echo "File instruksi 'BACA_SAYA.txt' telah dibuat.\n";

        echo "\033[32mBuild untuk cPanel berhasil dibuat di folder '{$buildDir}'.\033[0m\n";
    }
    
    private function createReadme($buildDir) {
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

3.  **KONFIGURASI & IMPORT DATABASE:**
    - Di cPanel, buka 'MySQL Databases' dan buat database baru beserta user-nya.
    - Buka file manager, navigasi ke direktori root (tempat Anda upload isi folder 1), lalu edit file '.env'. Sesuaikan konfigurasi DB_DATABASE, DB_USERNAME, dan DB_PASSWORD.
    - Kembali ke cPanel, buka 'phpMyAdmin'.
    - Pilih database yang baru Anda buat, klik tab 'Import', lalu upload dan jalankan file 'database.sql' yang ada di folder '1_upload_ke_ROOT_HOME'.

Deployment Anda sekarang seharusnya sudah live.
EOT;
        file_put_contents($buildDir . '/BACA_SAYA.txt', $instructions);
    }

    // --- Database Dump Methods ---

    private function getTables(): array
    {
        $stmt = $this->pdo->query('SHOW TABLES');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function dumpTableStructure($handle, string $table)
    {
        fwrite($handle, "--\n-- Struktur tabel untuk `{$table}`\n--\n\n");
        fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;
");
        
        $stmt = $this->pdo->query("SHOW CREATE TABLE `{$table}`");
        $createTableSql = $stmt->fetch(PDO::FETCH_ASSOC)['Create Table'];
        $stmt->closeCursor();

        // Ganti collation yang tidak kompatibel untuk support MySQL versi lama
        $createTableSql = str_replace('utf8mb4_0900_ai_ci', 'utf8mb4_unicode_ci', $createTableSql);

        fwrite($handle, $createTableSql . ";\n\n");
    }

    private function dumpTableData($handle, string $table)
    {
        $columnsString = $this->getColumnNames($table);
        $stmt = $this->pdo->query("SELECT * FROM `{$table}`");
        $columnCount = $stmt->columnCount();
        
        if ($stmt->rowCount() === 0) {
            $stmt->closeCursor();
            return;
        }

        fwrite($handle, "--\n-- Membuang data untuk tabel `{$table}`\n--\n\n");

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $values = [];
            for ($j = 0; $j < $columnCount; $j++) {
                if ($row[$j] === null) {
                    $values[] = 'NULL';
                } else {
                    $values[] = "'" . addslashes($row[$j]) . "'";
                }
            }
            fwrite($handle, "INSERT INTO `{$table}` ({$columnsString}) VALUES (" . implode(', ', $values) . ");\n");
        }
        
        $stmt->closeCursor();
        fwrite($handle, "\n");
    }

    private function getColumnNames(string $table): string
    {
        $stmt = $this->pdo->query("DESCRIBE `{$table}`");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stmt->closeCursor();
        return '`' . implode('`, `', $columns) . '`';
    }

    // --- File System Methods ---

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
