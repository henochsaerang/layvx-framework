<?php

namespace App\Commands;

use App\Core\App;
use App\Core\Command;
use PDO;

class MigrateCommand extends Command
{
    protected $signature = 'migrasi';
    protected $description = 'Menjalankan migrasi database.';

    private $pdo;

    public function handle(array $args = [])
    {
        $this->checkAndCreateDatabase();

        // Resolve PDO sekarang, karena database sudah pasti ada
        $this->pdo = \App\Core\App::getContainer()->resolve(\PDO::class);

        $this->ensureMigrationsTableExists();

        $executedMigrations = $this->getExecutedMigrations();
        $migrationFiles = $this->getMigrationFiles();

        $migrationsToRun = array_diff($migrationFiles, $executedMigrations);

        if (empty($migrationsToRun)) {
            echo "Tidak ada migrasi baru untuk dijalankan.\n";
            return;
        }

        foreach ($migrationsToRun as $migrationFile) {
            $this->runMigration($migrationFile);
        }

        echo "Migrasi selesai.\n";
    }

    private function checkAndCreateDatabase()
    {
        $config = config('database');
        $driver = $config['driver'];
        $host = $config['host'];
        $dbName = $config['db_name'];
        $user = $config['db_user'];
        $pass = $config['db_pass'];

        if ($driver !== 'mysql') {
            // Saat ini, hanya mysql yang didukung untuk pembuatan otomatis
            return;
        }

        try {
            // Terhubung tanpa menentukan nama database
            $tempPdo = new PDO("mysql:host=$host", $user, $pass);
            $tempPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Buat database jika belum ada
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");

            echo "Database '$dbName' siap.\n";
        } catch (\PDOException $e) {
            echo "Gagal terhubung ke server database: " . $e->getMessage() . "\n";
            exit(1); // Keluar jika tidak bisa terhubung ke server DB
        }
    }

    private function ensureMigrationsTableExists()
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    private function getExecutedMigrations()
    {
        $stmt = $this->pdo->query("SELECT migration FROM migrations");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getMigrationFiles()
    {
        $migrationPath = __DIR__ . '/../../database/tabel';
        if (!is_dir($migrationPath)) {
            mkdir($migrationPath, 0777, true);
        }
        $files = scandir($migrationPath);
        $phpFiles = array_filter($files, function ($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'php';
        });
        sort($phpFiles);
        return $phpFiles;
    }

    private function runMigration($migrationFile)
    {
        $migrationPath = __DIR__ . '/../../database/tabel';
        require_once $migrationPath . '/' . $migrationFile;

        $className = $this->getClassNameFromFileName($migrationFile);

        if (class_exists($className)) {
            $migration = new $className($this->pdo);
            $migration->up();

            $this->logMigration($migrationFile);

            echo "Migrasi dijalankan: {$migrationFile}\n";
        } else {
            echo "Error: Class {$className} tidak ditemukan di file {$migrationFile}.\n";
        }
    }

    private function logMigration($migrationFile)
    {
        $stmt = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
        $stmt->execute([$migrationFile]);
    }

    private function getClassNameFromFileName($fileName)
    {
        $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
        // Hapus bagian tanggal dan waktu (misal: '2023_01_01_000000_')
        $classNamePart = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $fileNameWithoutExtension);
        return str_replace('_', '', ucwords($classNamePart, '_'));
    }
}
