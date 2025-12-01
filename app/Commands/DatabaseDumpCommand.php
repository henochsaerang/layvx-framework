<?php

namespace App\Commands;

use App\Core\App;
use App\Core\Command;
use PDO;

class DatabaseDumpCommand extends Command
{
    protected $signature = 'buat:sql';
    protected $description = 'Mengekspor database saat ini ke file SQL (Backup).';

    private $pdo;

    public function handle(array $args = [])
    {
        $this->info("Memulai proses backup database...");

        try {
            $this->pdo = App::getContainer()->resolve(PDO::class);

            $dbConfig = config('database');
            $dbName = $dbConfig['database'];

            $backupDir = 'database/backups';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $fileName = sprintf('backup_%s_%s.sql', $dbName, date('Y-m-d_H-i-s'));
            $filePath = $backupDir . '/' . $fileName;

            $handle = fopen($filePath, 'w');
            if ($handle === false) {
                throw new \Exception("Tidak dapat membuat file backup.");
            }

            fwrite($handle, "-- LayVX SQL Dump\n");
            fwrite($handle, "-- Versi Framework: 1.0.0\n");
            fwrite($handle, "-- Host: {$dbConfig['host']}\n");
            fwrite($handle, "-- Database: {$dbName}\n");
            fwrite($handle, "-- Waktu: " . date('Y-m-d H:i:s') . "\n");
            fwrite($handle, "--\n\n");

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            $tables = $this->getTables();

            foreach ($tables as $table) {
                $this->info("Memproses tabel: {$table}...");
                $this->dumpTableStructure($handle, $table);
                $this->dumpTableData($handle, $table);
            }
            
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");

            fclose($handle);

            $this->info("------------------------------------------------");
            $this->info("Backup database berhasil disimpan di:");
            $this->info($filePath);
            $this->info("------------------------------------------------");

        } catch (\Exception $e) {
            $this->error("Terjadi kesalahan: " . $e->getMessage());
            // Clean up potentially created file on error
            if (isset($filePath) && file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

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

        fwrite($handle, $createTableSql . ";\n\n");
    }

    private function dumpTableData($handle, string $table)
    {
        $stmt = $this->pdo->query("SELECT * FROM `{$table}`");
        $rowCount = $stmt->rowCount();

        if ($rowCount === 0) {
            $stmt->closeCursor();
            return;
        }

        fwrite($handle, "--\n-- Membuang data untuk tabel `{$table}`\n--\n\n");
        
        $columnCount = $stmt->columnCount();

        for ($i = 0; $i < $rowCount; $i++) {
            $row = $stmt->fetch(PDO::FETCH_NUM);
            
            $values = [];
            for ($j = 0; $j < $columnCount; $j++) {
                if ($row[$j] === null) {
                    $values[] = 'NULL';
                } else {
                    // Escape the value
                    $values[] = "'" . addslashes($row[$j]) . "'";
                }
            }

            $columnNames = array_map(function($meta) {
                return '`' . $meta['name'] . '`';
            }, array_filter($stmt->fetchAll(PDO::FETCH_ASSOC), function($key) use ($stmt, $columnCount){
                $meta = $stmt->getColumnMeta($key);
                return $meta['name'];
            }, ARRAY_FILTER_USE_KEY));
            
            $columnsString = $this->getColumnNames($table);

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
}
