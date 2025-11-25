<?php

namespace App\Commands;

use App\Core\Command;
use PDO;
use PDOException;

class MigrateCommand extends Command {
    protected $signature = 'migrasi';
    protected $description = 'Menjalankan migrasi database yang tertunda';

    public function handle(array $args = []) {
        $config = require __DIR__ . '/../../config/database.php';
        $path = __DIR__ . '/../../database/tabel';

        try {
            $pdo_server = new PDO(
                "mysql:host={$config['host']}",
                $config['db_user'],
                $config['db_pass']
            );
            $pdo_server->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo_server->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$config['db_name']}'");
            if ($stmt->fetchColumn() === false) {
                echo "Database '{$config['db_name']}' not found. Creating database...\n";
                $pdo_server->exec("CREATE DATABASE `{$config['db_name']}`");
                echo "Database '{$config['db_name']}' created successfully.\n";
            }
            $pdo_server = null;

            $pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['db_name']}",
                $config['db_user'],
                $config['db_pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Database connection or creation failed: " . $e->getMessage() . "\n";
            exit(1);
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (id INT AUTO_INCREMENT PRIMARY KEY, migration VARCHAR(255) NOT NULL, batch INT NOT NULL)");
        $ran_migrations_stmt = $pdo->query("SELECT migration FROM migrations");
        $ran_migrations = $ran_migrations_stmt->fetchAll(PDO::FETCH_COLUMN);
        $files = scandir($path);
        $pending_migrations = [];
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && !in_array($file, $ran_migrations)) {
                $pending_migrations[] = $file;
            }
        }

        if (empty($pending_migrations)) {
            echo "Nothing to migrate.\n";
            return;
        }

        $batch = ($pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn() ?? 0) + 1;
        foreach ($pending_migrations as $migration_file) {
            echo "Migrating: {$migration_file}\n";
            require_once "{$path}/{$migration_file}";
            
            $rawClassName = pathinfo($migration_file, PATHINFO_FILENAME);
            $baseName = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $rawClassName);
            $parts = explode('_', $baseName);
            $verb = array_shift($parts);
            array_pop($parts);
            $modelName = str_replace('_', '', ucwords(implode('_', $parts), '_'));
            $classNamePrefix = ucfirst($verb);
            $className = $classNamePrefix . $modelName . 'Table';

            if (class_exists($className)) {
                $migration = new $className($pdo);
                $migration->up();
                $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                $stmt->execute([$migration_file, $batch]);
                echo "Migrated:  {$migration_file}\n";
            }
        }
        echo "Migration completed.\n";
    }
}
