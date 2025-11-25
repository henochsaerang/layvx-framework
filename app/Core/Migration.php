<?php

namespace App\Core;

use PDO;
use App\Core\Column;

// app/Core/Migration.php

abstract class Migration {
    protected $pdo;
    protected $tableToCreate; // Stores table name for current migration context

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Executes the migration (creates tables, adds columns, etc.).
     */
    abstract public function up();

    /**
     * Reverts the migration (drops tables, removes columns, etc.).
     */
    abstract public function down();

    /**
     * Builds and executes a CREATE TABLE SQL statement.
     *
     * @param string $tableName The name of the table to create.
     * @param array $columns An array of Column objects or arrays of Column objects (for timestamps helper).
     */
    protected function createTable(string $tableName, array $columns, array $compositeUniqueKeys = []) {
        $this->tableToCreate = $tableName; // Set current table name
        $sqlColumns = [];
        $foreignKeys = []; // Collect FK definitions

        foreach ($columns as $item) {
            // Handle single Column object or array of Column objects (for timestamps helper)
            if (is_array($item)) {
                foreach ($item as $column) {
                    if ($column instanceof Column) {
                        $sqlColumns[] = $column->build();
                        if ($column->isForeignKey()) {
                            $foreignKeys[] = $column; // Collect Column object if it's an FK
                        }
                    }
                }
            } elseif ($item instanceof Column) {
                $sqlColumns[] = $item->build();
                if ($item->isForeignKey()) {
                    $foreignKeys[] = $item; // Collect Column object if it's an FK
                }
            }
        }
        
        // First, build and execute CREATE TABLE
        $createTableSql = "CREATE TABLE `{$tableName}` (\n" . implode(",\n", $sqlColumns) . "\n)";
        $this->pdo->exec($createTableSql);

        // Then, add foreign key constraints as separate ALTER TABLE statements
        foreach ($foreignKeys as $fkColumn) {
            $fkSql = $fkColumn->buildForeignKey($tableName);
            $this->pdo->exec("ALTER TABLE `{$tableName}` {$fkSql}");
        }

        // Finally, add composite unique keys
        foreach ($compositeUniqueKeys as $uniqueColumns) {
            if (is_array($uniqueColumns) && !empty($uniqueColumns)) {
                $uniqueColsSql = "`" . implode("`,`", $uniqueColumns) . "`";
                $constraintName = "uq_{$tableName}_" . implode('_', $uniqueColumns); // Generate a consistent constraint name
                $this->pdo->exec("ALTER TABLE `{$tableName}` ADD CONSTRAINT `{$constraintName}` UNIQUE ({$uniqueColsSql})");
            }
        }
    }

    /**
     * Builds and executes a DROP TABLE IF EXISTS SQL statement.
     *
     * @param string $tableName The name of the table to drop.
     * @param array $compositeUniqueKeys Optional. An array of arrays, each defining a composite unique key.
     */
    protected function dropTable(string $tableName, array $compositeUniqueKeys = []) {
        // Drop composite unique keys first if they were added
        foreach ($compositeUniqueKeys as $uniqueColumns) {
            if (is_array($uniqueColumns) && !empty($uniqueColumns)) {
                $constraintName = "uq_{$tableName}_" . implode('_', $uniqueColumns);
                // MySQL automatically creates an index when a unique constraint is added,
                // and it can be dropped by its name or the generated index name.
                // It's safer to drop by constraint name if it was explicitly named.
                $this->pdo->exec("ALTER TABLE `{$tableName}` DROP INDEX `{$constraintName}`");
            }
        }
        $this->pdo->exec("DROP TABLE IF EXISTS `{$tableName}`");
    }
}
