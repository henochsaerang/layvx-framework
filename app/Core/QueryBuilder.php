<?php

namespace App\Core;

use PDO;

// app/Core/QueryBuilder.php

class QueryBuilder {
    protected $pdo;
    protected $table;
    protected $select = ['*'];
    protected $joins = [];
    protected $wheres = [];
    protected $orderBy = [];
    protected $limit = null;
    protected $offset = null;
    protected $params = []; // Will store values, not typed string
    protected $modelClass;
    protected $eagerLoads = [];

    public function __construct(PDO $pdo, string $modelClass) {
        $this->pdo = $pdo;
        $this->modelClass = $modelClass;
    }

    public function table(string $table, string $alias = null): self {
        $this->table = $alias ? "`{$table}` AS `{$alias}`" : "`{$table}`";
        return $this;
    }

    public function select(...$columns): self {
        $this->select = $columns;
        return $this;
    }

    /**
     * Build a JOIN clause.
     * @param string $table The table name.
     * @param string $on The ON clause condition as a raw SQL string (e.g., 'table1.col1 = table2.col2 AND table1.col3 = table2.col4').
     * @param string $type The type of JOIN (e.g., 'INNER', 'LEFT').
     * @param string|null $alias Optional alias for the joined table.
     * @return $this
     */
    public function join(string $table, string $on, string $type = 'INNER', string $alias = null): self {
        $joinTable = $alias ? "`{$table}` AS `{$alias}`" : "`{$table}`";
        $this->joins[] = "{$type} JOIN {$joinTable} ON {$on}";
        return $this;
    }

    /**
     * Build a LEFT JOIN clause.
     * @param string $table The table name.
     * @param string $on The ON clause condition as a raw SQL string.
     * @param string|null $alias Optional alias for the joined table.
     * @return $this
     */
    public function leftJoin(string $table, string $on, string $alias = null): self {
        return $this->join($table, $on, 'LEFT', $alias);
    }

    public function where(string $column, string $operator, $value): self {
        $columnSql = strpos($column, '.') === false ? "`{$column}`" : $column;
        $this->wheres[] = "{$columnSql} {$operator} ?";
        $this->params[] = $value;
        return $this;
    }
    
    public function whereEquals(string $column, $value): self {
        return $this->where($column, '=', $value);
    }

    public function whereIn(string $column, array $values): self {
        if (empty($values)) {
            $this->wheres[] = "0 = 1";
            return $this;
        }
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $columnSql = strpos($column, '.') === false ? "`{$column}`" : $column;
        $this->wheres[] = "{$columnSql} IN ({$placeholders})";
        $this->params = array_merge($this->params, $values);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self {
        $columnSql = strpos($column, '.') === false ? "`{$column}`" : $column;
        $this->orderBy[] = "{$columnSql} {$direction}";
        return $this;
    }

    public function limit(int $limit): self {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self {
        $this->offset = $offset;
        return $this;
    }

    public function eagerLoad(array $relations): self {
        $this->eagerLoads = $relations;
        return $this;
    }

    public function get(): array {
        $mainQueryResults = $this->executeMainQuery();
        if (empty($mainQueryResults) || empty($this->eagerLoads)) {
            return $mainQueryResults;
        }
        return $this->runEagerLoads($mainQueryResults);
    }

    public function first(): ?array {
        $this->limit(1);
        $result = $this->get();
        return empty($result) ? null : $result[0];
    }

    public function update(array $data): bool {
        $setParts = [];
        $values = [];
        foreach ($data as $column => $value) {
            $setParts[] = "`{$column}` = ?";
            $values[] = $value;
        }
        
        $setClause = implode(", ", $setParts);
        
        $query = "UPDATE {$this->table} SET {$setClause}";
        
        if (!empty($this->wheres)) {
            $query .= " WHERE " . implode(' AND ', $this->wheres);
            $values = array_merge($values, $this->params); // Add WHERE clause params
        }

        $stmt = $this->pdo->prepare($query);
        
        if (!empty($values)) {
            return $stmt->execute($values);
        }
        return $stmt->execute();
    }

    public function delete(): bool {
        $query = "DELETE FROM {$this->table}";
        if (!empty($this->wheres)) {
            $query .= " WHERE " . implode(' AND ', $this->wheres);
        }

        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($this->params);
    }

    public function insertOrUpdate(array $data, array $updateMap): bool {
        $this->wheres = [];
        $this->orderBy = [];
        $this->limit = null;
        $this->offset = null;
        $this->params = [];

        $insertColumns = [];
        $insertPlaceholders = [];
        $insertValues = [];

        foreach ($data as $col => $val) {
            $insertColumns[] = "`{$col}`";
            if (is_string($val) && (strtoupper($val) === 'NOW()' || strtoupper($val) === 'CURRENT_TIMESTAMP')) {
                $insertPlaceholders[] = $val;
            } else {
                $insertPlaceholders[] = "?";
                $insertValues[] = $val;
            }
        }
        
        $columnsSql = implode(", ", $insertColumns);
        $placeholdersSql = implode(", ", $insertPlaceholders);

        $query = "INSERT INTO {$this->table} ({$columnsSql}) VALUES ({$placeholdersSql})";
        $allValues = $insertValues; // Default values for simple INSERT

        // Only add ON DUPLICATE KEY UPDATE if updateMap is not empty
        if (!empty($updateMap)) {
            $updateParts = [];
            $updateValues = [];
            
            foreach ($updateMap as $col => $val) {
                if (is_string($val) && (strtoupper($val) === 'NOW()' || strtoupper($val) === 'CURRENT_TIMESTAMP' || str_starts_with(strtoupper($val), 'VALUES('))) {
                    $updateParts[] = "`{$col}` = {$val}";
                } else {
                    $updateParts[] = "`{$col}` = ?";
                    $updateValues[] = $val;
                }
            }
            $updateClause = implode(", ", $updateParts);
            $query .= " ON DUPLICATE KEY UPDATE {$updateClause}";
            $allValues = array_merge($insertValues, $updateValues); // Values for INSERT + UPDATE
        }
        
        $stmt = $this->pdo->prepare($query);
        
        if (!empty($allValues)) {
            return $stmt->execute($allValues);
        }
        return $stmt->execute(); // Execute with no params if allValues is empty (e.g. INSERT with NOW() only)
    }

    protected function executeMainQuery(): array {
        $sql = "SELECT " . implode(', ', $this->select);
        $sql .= " FROM {$this->table}";

        foreach ($this->joins as $join) {
            $sql .= " {$join}";
        }

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function runEagerLoads(array $parentModels): array {
        // Need to convert array of assoc arrays to array of objects for easier access
        $parentModels = array_map(fn($item) => (object)$item, $parentModels);
        
        foreach ($this->eagerLoads as $relationName) {
            $relationDefinition = $this->modelClass::getRelationDefinition($relationName);
            if ($relationDefinition === null) {
                throw new Exception("Relation '{$relationName}' not defined on model '{$this->modelClass}'.");
            }

            $type = $relationDefinition['type'];
            $relatedModelClass = $relationDefinition['related'];
            $relationForeignKey = $relationDefinition['foreignKey']; // FK on current model for belongsTo, or related model for hasMany
            
            $relationLocalKey = null; // Local key on current model for hasMany
            $relationOwnerKey = null; // PK on related model for belongsTo

            if ($type === 'hasMany') {
                $relationLocalKey = $relationDefinition['localKey'];
            } elseif ($type === 'belongsTo') {
                $relationOwnerKey = $relationDefinition['ownerKey'];
            }

            if ($type === 'hasMany') {
                $parentIds = array_column($parentModels, $relationLocalKey);
                if (empty($parentIds)) continue; // No parent IDs to query for

                // Query related model for records where foreignKey IN (parentIds)
                $relatedModels = $relatedModelClass::query()->whereIn($relationForeignKey, $parentIds)->get();
                // Group related models by foreign key
                $groupedRelated = [];
                foreach ($relatedModels as $related) {
                    $groupedRelated[$related[$relationForeignKey]][] = $related;
                }

                // Map related models to parent models
                foreach ($parentModels as $parent) {
                    $parent->{$relationName} = $groupedRelated[$parent->{$relationLocalKey}] ?? [];
                }
            } elseif ($type === 'belongsTo') {
                $parentIds = array_unique(array_column($parentModels, $relationForeignKey)); // FK on current model
                 if (empty($parentIds)) continue; // No parent IDs to query for

                // Query related model for where ownerKey IN (parentIds)
                $relatedModels = $relatedModelClass::query()->whereIn($relationOwnerKey, $parentIds)->get();
                $relatedMap = array_combine(array_column($relatedModels, $relationOwnerKey), $relatedModels);
                // Map related models to parent models
                foreach ($parentModels as $parent) {
                    $parent->{$relationName} = $relatedMap[$parent->{$relationForeignKey}] ?? null;
                }
            }
        }
        // Convert objects back to assoc arrays if that's the expected return type
        return array_map(fn($item) => (array)$item, $parentModels);
    }

    protected function getType($value): string {
        // PDO handles type inference fairly well, no need for explicit types for execute($params)
        // This method is now unused, but keeping for reference if bindParam is needed later.
        if (is_int($value)) return 'i';
        if (is_float($value)) return 'd';
        return 's'; // Default to string
    }
}
