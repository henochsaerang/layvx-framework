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

    public function __construct()
    {
        $this->pdo = App::getContainer()->resolve(PDO::class);
    }

    public function handle(array $args = [])
    {
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
        $fileNameWithoutExtension = substr($fileName, 0, -4);
        $namePart = implode('_', array_slice(explode('_', $fileNameWithoutExtension), 4));
        return str_replace('_', '', ucwords($namePart, '_'));
    }
}
