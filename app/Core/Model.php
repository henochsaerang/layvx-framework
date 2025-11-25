<?php

namespace App\Core;

use PDO;

class Model {
    protected static $conn;
    protected static $table; // To be defined by child classes (e.g., 'users')
    protected static $primaryKey = 'id'; // Default primary key name
    protected static $fillable = []; // Kolom yang dapat diisi secara massal (mass-assignable)

    // Stores the relationship definitions for each model
    protected static $relations = [];
    protected static $initializedRelations = false; // Flag to ensure relations are defined only once

    /**
     * Get the fillable columns for the current model.
     *
     * @return array
     */
    public static function getFillable(): array {
        return static::$fillable ?? [];
    }

    /**
     * Filter data array based on the model's fillable properties.
     *
     * @param array $data
     * @return array
     */
    protected static function filterFillable(array $data): array {
        $fillable = static::getFillable();
        if (empty($fillable)) {
            // Jika fillable kosong, semua kolom diizinkan (atau diserahkan ke DB)
            // Atau, Anda bisa memilih untuk tidak mengizinkan apa pun jika fillable kosong dan mode strict
            return $data; 
        }
        return array_intersect_key($data, array_flip($fillable));
    }

    /**
     * Helper to add a relation definition to the model's static $relations array.
     */
    protected static function _addRelation(string $name, array $definition) {
        static::$relations[$name] = $definition;
    }

    /**
     * Internal helper to define a hasMany relationship.
     */
    protected static function _defineHasMany(string $related, string $foreignKey = null, string $localKey = null): array {
        $relatedInstance = new $related(); // Instantiate to get metadata of related model
        $foreignKey = $foreignKey ?? strtolower((new ReflectionClass(new static()))->getShortName()) . '_id'; // Default: current_model_id (e.g., user_id)
        $localKey = $localKey ?? (new static())->getPrimaryKey(); // Default: parent_model_primary_key (e.g., id)

        return [
            'type' => 'hasMany',
            'related' => $related,
            'foreignKey' => $foreignKey,
            'localKey' => $localKey
        ];
    }

    /**
     * Internal helper to define a belongsTo relationship.
     */
    protected static function _defineBelongsTo(string $related, string $foreignKey = null, string $ownerKey = null): array {
        $relatedInstance = new $related(); // Instantiate to get metadata of related model
        $foreignKey = $foreignKey ?? strtolower((new ReflectionClass($relatedInstance))->getShortName()) . '_id'; // Default: related_model_id (e.g., course_id)
        $ownerKey = $ownerKey ?? $relatedInstance->getPrimaryKey(); // Default: related_model_primary_key (e.g., id)

        return [
            'type' => 'belongsTo',
            'related' => $related,
            'foreignKey' => $foreignKey,
            'ownerKey' => $ownerKey
        ];
    }

    /**
     * NEW: Public static helper for 'Satu Ke Banyak' relationship definition.
     */
    public static function SatuKeBanyak(string $name, string $related, string $foreignKey = null, string $localKey = null) {
        static::_addRelation($name, static::_defineHasMany($related, $foreignKey, $localKey));
    }

    /**
     * NEW: Public static helper for 'Banyak Ke Satu' relationship definition.
     */
    public static function BanyakKeSatu(string $name, string $related, string $foreignKey = null, string $ownerKey = null) {
        static::_addRelation($name, static::_defineBelongsTo($related, $foreignKey, $ownerKey));
    }

    /**
     * Get a relation definition by name.
     *
     * @param string $name
     * @return array|null
     */
    public static function getRelationDefinition(string $name): ?array {
        // Ensure relationships are initialized for the calling model
        if (!static::$initializedRelations && method_exists(static::class, 'defineRelationships')) {
            static::defineRelationships();
            static::$initializedRelations = true;
        }
        return static::$relations[$name] ?? null;
    }

    // NEW: Placeholder for child models to define their relationships
    // Child models should implement this method.
    protected static function defineRelationships() {
        // Example:
        // static::SatuKeBanyak('posts', Post::class, 'user_id');
        // static::BanyakKeSatu('user', User::class, 'user_id');
    }

    public static function connect(): PDO { // Return type hint changed to PDO
        if (!self::$conn) {
            $config = require(__DIR__ . '/../../config/database.php');
            
            $dsn = "mysql:host={$config['host']};dbname={$config['db_name']};charset=utf8mb4";
            try {
                $pdo = new PDO(
                    $dsn,
                    $config['db_user'],
                    $config['db_pass']
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Fetch associative arrays by default
                self::$conn = $pdo;
            } catch (PDOException $e) {
                throw new Exception("Koneksi Gagal: " . $e->getMessage());
            }
        }
        return self::$conn;
    }

    /**
     * Get a new QueryBuilder instance for the model's table.
     */
    public static function query(): QueryBuilder {
        $instance = new static();
        // Pass the current model class name to the QueryBuilder
        return (new QueryBuilder(self::connect(), static::class))->table($instance->getTable());
    }

    /**
     * New: Start a query with relationships to be eager loaded.
     *
     * @param string ...$relations The names of the relations to eager load.
     * @return QueryBuilder
     */
    public static function with(...$relations): QueryBuilder {
        $builder = static::query();
        $builder->eagerLoad($relations); // Pass relations to QueryBuilder
        return $builder;
    }

    /**
     * Get all records from the model's table.
     */
    public static function all() {
        return static::query()->get();
    }

    /**
     * Find a record by its primary key.
     */
    public static function find($id) {
        $instance = new static();
        return static::query()->whereEquals($instance->getPrimaryKey(), $id)->first();
    }

    /**
     * Find records by a given column and value.
     */
    public static function where($column, $value) {
        return static::query()->whereEquals($column, $value);
    }
    
    /**
     * Create a new record in the table.
     */
    public static function create(array $data) {
        $instance = new static();
        $filteredData = static::filterFillable($data); // Filter data
        return static::query()->insertOrUpdate($filteredData, []); // Pass empty updateMap for pure create
    }

    /**
     * Update an existing record by its primary key.
     */
    public static function update($id, array $data) {
        $instance = new static();
        $filteredData = static::filterFillable($data); // Filter data
        return static::query()->table($instance->getTable())->whereEquals($instance->getPrimaryKey(), $id)->update($filteredData); // Needs QueryBuilder::update()
    }

    /**
     * Update records based on a WHERE condition.
     */
    public static function updateWhere($whereColumn, $whereValue, array $data) {
        $instance = new static();
        $filteredData = static::filterFillable($data); // Filter data
        return static::query()->table($instance->getTable())->whereEquals($whereColumn, $whereValue)->update($filteredData); // Needs QueryBuilder::update()
    }

    /**
     * Delete a record by its primary key.
     */
    public static function delete($id) {
        $instance = new static();
        return static::query()->table($instance->getTable())->whereEquals($instance->getPrimaryKey(), $id)->delete(); // Needs QueryBuilder::delete()
    }

    /**
     * Execute a callback within a database transaction.
     *
     * @param callable $callback The function to execute within the transaction.
     * @return bool True if transaction committed, false if rolled back due to exception.
     * @throws Exception If the connection fails or an unexpected error occurs during transaction setup.
     */
    public static function transaction(callable $callback): bool {
        $conn = self::connect();
        $conn->beginTransaction();

        try {
            $result = $callback($conn); // Pass connection to the callback if needed
            $conn->commit();
            return $result !== false; // Assume false from callback means failure
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Database transaction failed: " . $e->getMessage());
            // For now, return false as per the original user method's return type
            return false;
        }
    }

    /**
     * Helper to get the table name (overridable by child classes).
     */
    public function getTable() { // Changed to public for ReflectionClass in belongsTo/hasMany
        // If $table is explicitly set, use it. Otherwise, infer from class name.
        if (static::$table) {
            return static::$table;
        }
        // Basic pluralization for inferred table name
        return strtolower((new ReflectionClass($this))->getShortName()) . 's';
    }

    /**
     * Helper to get the primary key name (overridable by child classes).
     */
    public function getPrimaryKey() { // Changed to public for ReflectionClass in belongsTo/hasMany
        return static::$primaryKey;
    }
}
