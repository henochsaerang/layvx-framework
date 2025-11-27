<?php

namespace App\Commands;

use App\Core\Command;

class MakeModelCommand extends Command {
    protected $signature = 'buat:model';
    protected $description = 'Membuat class Model baru. Gunakan flag -t untuk membuat migrasi juga.';

    public function handle(array $args = []) {
        $modelName = $args[0] ?? null;
        if (empty($modelName)) {
            echo "Error: Please provide a name for the model.\n";
            echo "Usage: php layvx buat:model <ModelName> [-t]\n";
            exit(1);
        }

        $modelName = ucfirst($modelName);
        $basePath = __DIR__ . '/../../'; // From app/Commands to project root
        
        // --- Model Directory Setup ---
        $modelDirectory = $basePath . 'app/Models';
        if (!is_dir($modelDirectory)) {
            if (!mkdir($modelDirectory, 0755, true)) {
                echo "Error: Failed to create directory {$modelDirectory}.\n";
                exit(1);
            }
            echo "Directory created: {$modelDirectory}\n";
        }
        
        $filepath = $modelDirectory . '/' . $modelName . '.php';

        if (file_exists($filepath)) {
            echo "Error: Model {$modelName} already exists.\n";
            exit(1);
        }

        $tableName = strtolower($modelName) . 's'; // Simple pluralization

        $stub = <<<PHP
<?php

namespace App\Models;

use App\Core\Model;

class {$modelName} extends Model {
    protected static \$table = '{$tableName}';
}

PHP;

        if (file_put_contents($filepath, $stub) === false) {
            echo "Error: Could not create model file.\n";
            exit(1);
        }
        echo "Model created successfully: {$filepath}\n";

        if (in_array('-t', $args)) {
            $migrationDirectory = $basePath . 'database/tabel';
            $this->createMigration($tableName, $migrationDirectory);
        }
    }

    private function createMigration($name, $path) {
        // --- Migration Directory Setup ---
        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true)) {
                echo "Error: Failed to create directory {$path}.\n";
                return;
            }
            echo "Directory created: {$path}\n";
        }
        
        $type = 'create';
        $timestamp = date('Y_m_d_His');
        $className = 'Create' . str_replace('_', '', ucwords($name, '_')) . 'Table';
        $filename = "{$timestamp}_create_{$name}_table.php";

        // Use single-quoted strings with placeholders to avoid HEREDOC parsing issues
        $up_stub_template = '        $this->createTable(\'__TABLE_NAME__\', [
            col(\'id\')->id(), // Auto-incrementing primary key
            timestamps(), // created_at and updated_at
        ]);';
        $down_stub_template = '        $this->dropTable(\'__TABLE_NAME__\');';

        // Replace placeholders with the actual table name
        $up_stub_content = str_replace('__TABLE_NAME__', $name, $up_stub_template);
        $down_stub_content = str_replace('__TABLE_NAME__', $name, $down_stub_template);

        $filepath = "{$path}/{$filename}";

        $stub = <<<PHP
<?php

use App\Core\Migration;

class {$className} extends Migration {
    public function up() {
        {$up_stub_content}
    }

    public function down() {
        {$down_stub_content}
    }
}

PHP;

        if (file_put_contents($filepath, $stub) === false) {
            echo "Error: Could not create migration file for model.\n";
            return;
        }

        echo "Created Migration: {$filename}\n";
    }
}