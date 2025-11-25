<?php

namespace App\Core;

// app/Core/Column.php (Moved from helpers.php)

class Column {
    private $name;
    private $type;
    private $length = null;
    private $nullable = false;
    private $unique = false;
    private $primary = false;
    private $autoIncrement = false;
    private $default = null;
    private $onUpdate = null; // For TIMESTAMP
    private $isForeignKey = false;
    private $foreignTable = null;
    private $foreignColumn = null;
    private $onDelete = null;
    private $onUpdateFK = null;

    public function __construct(string $name = null) {
        $this->name = $name;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function isPrimary(): bool {
        return $this->primary;
    }

    public function isForeignKey(): bool {
        return $this->isForeignKey;
    }

    public function getForeignTable(): ?string {
        return $this->foreignTable;
    }

    public function getForeignColumn(): ?string {
        return $this->foreignColumn;
    }

    public function getOnDelete(): ?string {
        return $this->onDelete;
    }

    public function getOnUpdateFK(): ?string {
        return $this->onUpdateFK;
    }

    // Column Types
    public function increments(): self {
        $this->type = 'INT';
        $this->autoIncrement = true;
        $this->primary = true;
        return $this;
    }
    public function id(): self {
        return $this->increments();
    }

    public function string(int $length = 255): self {
        $this->type = 'VARCHAR';
        $this->length = $length;
        return $this;
    }

    public function text(): self {
        $this->type = 'TEXT';
        return $this;
    }

    public function integer(): self {
        $this->type = 'INT';
        return $this;
    }
    
    public function enum(array $values): self {
        $this->type = 'ENUM';
        $this->length = "'" . implode("','", array_map('addslashes', $values)) . "'";
        return $this;
    }

    public function timestamp(): self {
        $this->type = 'TIMESTAMP';
        return $this;
    }

    // Modifiers
    public function nullable(): self {
        $this->nullable = true;
        return $this;
    }

    public function notNullable(): self {
        $this->nullable = false;
        return $this;
    }

    public function unique(): self {
        $this->unique = true;
        return $this;
    }

    public function primary(): self {
        $this->primary = true;
        return $this;
    }

    public function default($value): self {
        if (is_string($value) && (strtoupper($value) === 'CURRENT_TIMESTAMP' || strtoupper($value) === 'NULL')) {
            $this->default = $value;
        } elseif (is_numeric($value)) {
            $this->default = $value;
        } else {
            $this->default = "'" . addslashes($value) . "'";
        }
        return $this;
    }

    public function onUpdate(string $value = 'CURRENT_TIMESTAMP'): self {
        $this->onUpdate = $value;
        return $this;
    }

    public function foreign(): self {
        $this->isForeignKey = true;
        return $this;
    }

    public function references(string $column): self {
        $this->foreignColumn = $column;
        return $this;
    }

    public function on(string $table): self {
        $this->foreignTable = $table;
        return $this;
    }

    public function onDelete(string $action): self {
        $action = strtoupper($action);
        if (!in_array($action, ['CASCADE', 'SET NULL', 'RESTRICT', 'NO ACTION'])) {
            throw new Exception("Invalid ON DELETE action: {$action}. Allowed: CASCADE, SET NULL, RESTRICT, NO ACTION.");
        }
        $this->onDelete = $action;
        return $this;
    }

    public function onUpdateFK(string $action): self {
        $action = strtoupper($action);
        if (!in_array($action, ['CASCADE', 'SET NULL', 'RESTRICT', 'NO ACTION'])) {
            throw new Exception("Invalid ON UPDATE action for foreign key: {$action}. Allowed: CASCADE, SET NULL, RESTRICT, NO ACTION.");
        }
        $this->onUpdateFK = $action;
        return $this;
    }

    public function build(): string {
        if ($this->name === null) {
            throw new Exception("Column name is required for type '{$this->type}'.");
        }

        $definition = "`{$this->name}` ";

        switch ($this->type) {
            case 'VARCHAR':
                $definition .= "VARCHAR({$this->length})";
                break;
            case 'ENUM':
                $definition .= "ENUM({$this->length})";
                break;
            case 'INT':
                $definition .= "INT";
                break;
            case 'TEXT':
                $definition .= "TEXT";
                break;
            case 'TIMESTAMP':
                $definition .= "TIMESTAMP";
                break;
            default:
                throw new Exception("Unsupported column type: {$this->type}");
        }
        
        if ($this->autoIncrement) {
            $definition .= " AUTO_INCREMENT";
        }
        if ($this->primary) {
            $definition .= " PRIMARY KEY";
        }
        
        if ($this->nullable) {
            $definition .= " NULL";
        } else {
            if (!$this->primary) {
                $definition .= " NOT NULL";
            }
        }

        if ($this->default !== null) {
            $definition .= " DEFAULT {$this->default}";
        }
        if ($this->onUpdate !== null) {
            $definition .= " ON UPDATE {$this->onUpdate}";
        }
        if ($this->unique && !$this->primary) {
            $definition .= " UNIQUE";
        }

        return $definition;
    }

    public function buildForeignKey(string $currentTableName): string {
        if (!$this->isForeignKey || $this->foreignTable === null || $this->foreignColumn === null || $this->name === null) {
            throw new Exception("Incomplete foreign key definition for column '{$this->name}'. Missing foreignTable, foreignColumn or isForeignKey not set.");
        }
        $constraintName = "fk_{$currentTableName}_{$this->name}_{$this->foreignTable}_{$this->foreignColumn}";
        $sql = "ADD CONSTRAINT `{$constraintName}` FOREIGN KEY (`{$this->name}`) REFERENCES `{$this->foreignTable}` (`{$this->foreignColumn}`)";
        if ($this->onDelete !== null) {
            $sql .= " ON DELETE {$this->onDelete}";
        }
        if ($this->onUpdateFK !== null) {
            $sql .= " ON UPDATE {$this->onUpdateFK}";
        }
        return $sql;
    }
}