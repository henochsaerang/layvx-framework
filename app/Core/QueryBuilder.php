<?php

namespace App\Core;

use PDO;
use Exception;

class QueryBuilder {
    protected $pdo;
    protected $table;
    protected $select = ['*'];
    protected $joins = [];
    protected $wheres = [];
    protected $orderBy = [];
    protected $limit = null;
    protected $offset = null;
    protected $params = [];
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

    public function join(string $table, string $on, string $type = 'INNER', string $alias = null): self {
        $joinTable = $alias ? "`{$table}` AS `{$alias}`" : "`{$table}`";
        $this->joins[] = "{$type} JOIN {$joinTable} ON {$on}";
        return $this;
    }

    public function leftJoin(string $table, string $on, string $alias = null): self {
        return $this->join($table, $on, 'LEFT', $alias);
    }

    public function where(string $column, $operator = null, $value = null): self {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

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

    /**
     * @return Model[]
     */
    public function get(): array {
        $models = $this->executeMainQuery();

        if (!empty($models) && !empty($this->eagerLoads)) {
            $this->runEagerLoads($models);
        }

        return $models;
    }

    public function first(): ?Model {
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
            $values = array_merge($values, $this->params);
        }
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($values ?: []);
    }

    public function delete(): bool {
        $query = "DELETE FROM {$this->table}";
        if (!empty($this->wheres)) {
            $query .= " WHERE " . implode(' AND ', $this->wheres);
        }
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($this->params);
    }

    public function insertOrUpdate(array $data, array $updateMap = []): bool {
        // Handle INSERT operation
        $columns = implode('`, `', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $values = array_values($data);

        $query = "INSERT INTO {$this->table} (`{$columns}`) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($values);
    }

    /**
     * @return Model[]
     */
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
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $models = [];
        foreach ($results as $row) {
            $models[] = new $this->modelClass($row);
        }
        return $models;
    }

    /**
     * @param Model[] $parentModels
     * @return void
     * @throws Exception
     */
    protected function runEagerLoads(array $parentModels): void {
        foreach ($this->eagerLoads as $relationName) {
            $relationDefinition = $this->modelClass::getRelationDefinition($relationName);
            if ($relationDefinition === null) {
                throw new Exception("Relation '{$relationName}' not defined on model '{$this->modelClass}'.");
            }

            $type = $relationDefinition['type'];
            $relatedModelClass = $relationDefinition['related'];

            if ($type === 'hasMany') {
                $foreignKey = $relationDefinition['foreignKey'];
                $localKey = $relationDefinition['localKey'];
                $parentIds = array_map(fn($model) => $model->{$localKey}, $parentModels);
                if (empty($parentIds)) continue;

                $relatedModels = $relatedModelClass::query()->whereIn($foreignKey, $parentIds)->get();
                
                $groupedRelated = [];
                foreach ($relatedModels as $related) {
                    $groupedRelated[$related->{$foreignKey}][] = $related;
                }

                foreach ($parentModels as $parent) {
                    $parent->relationships[$relationName] = $groupedRelated[$parent->{$localKey}] ?? [];
                }

            } elseif ($type === 'belongsTo') {
                $foreignKey = $relationDefinition['foreignKey'];
                $ownerKey = $relationDefinition['ownerKey'];
                $parentIds = array_unique(array_map(fn($model) => $model->{$foreignKey}, $parentModels));
                if (empty($parentIds)) continue;
                
                $relatedModels = $relatedModelClass::query()->whereIn($ownerKey, $parentIds)->get();

                $relatedMap = [];
                foreach ($relatedModels as $related) {
                    $relatedMap[$related->{$ownerKey}] = $related;
                }

                foreach ($parentModels as $parent) {
                    $parent->relationships[$relationName] = $relatedMap[$parent->{$foreignKey}] ?? null;
                }
            } elseif ($type === 'belongsToMany') {
                $pivotTable = $relationDefinition['pivotTable'];
                $foreignPivotKey = $relationDefinition['foreignPivotKey']; // e.g., post_id
                $relatedPivotKey = $relationDefinition['relatedPivotKey']; // e.g., tag_id
                $parentKey = $relationDefinition['parentKey']; // e.g., id on posts
                $relatedKey = $relationDefinition['relatedKey']; // e.g., id on tags

                $parentIds = array_map(fn($model) => $model->{$parentKey}, $parentModels);
                if (empty($parentIds)) continue;

                // 1. Get all relevant records from the pivot table
                $pivotQuery = "SELECT * FROM `{$pivotTable}` WHERE `{$foreignPivotKey}` IN (" . implode(',', array_fill(0, count($parentIds), '?')) . ")";
                $stmt = $this->pdo->prepare($pivotQuery);
                $stmt->execute($parentIds);
                $pivotRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($pivotRows)) continue;

                // 2. Get all unique IDs of the related model from the pivot table results
                $relatedIds = array_unique(array_column($pivotRows, $relatedPivotKey));

                // 3. Fetch all the related models in a single query
                $relatedModels = $relatedModelClass::query()->whereIn($relatedKey, $relatedIds)->get();
                $relatedMap = [];
                foreach($relatedModels as $model) {
                    $relatedMap[$model->{$relatedKey}] = $model;
                }

                // 4. Map the related models back to the parent models
                $groupedRelated = [];
                foreach($pivotRows as $pivotRow) {
                    $parentId = $pivotRow[$foreignPivotKey];
                    $relatedId = $pivotRow[$relatedPivotKey];
                    if (isset($relatedMap[$relatedId])) {
                        $groupedRelated[$parentId][] = $relatedMap[$relatedId];
                    }
                }

                foreach ($parentModels as $parent) {
                    $parent->relationships[$relationName] = $groupedRelated[$parent->{$parentKey}] ?? [];
                }
            }
        }
    }
}
