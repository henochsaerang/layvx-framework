<?php

namespace App\Commands;

use App\Core\Command;

class MakeMigrationCommand extends Command {
    protected $signature = 'buat:tabel';
    protected $description = 'Membuat file migrasi untuk membuat atau menghapus tabel baru';

    public function handle(array $args = []) {
        $command = $args['command'] ?? 'buat:tabel';
        $name = $args[0] ?? null;

        if (empty($name)) {
            echo "Error: Please provide a name for the table.\n";
            echo "Usage: layvx {$command} <nama_tabel>\n";
            exit(1);
        }

        $type = ($command === 'buat:tabel') ? 'create' : 'drop';
        
        $basePath = __DIR__ . '/../../';
        $directory = $basePath . 'database/tabel'; // Directory path

        // Check if directory exists, create if not
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                echo "Error: Failed to create directory {$directory}.\n";
                exit(1);
            }
            echo "Directory created: {$directory}\n";
        }
        
        $timestamp = date('Y_m_d_His');
        $className = '';
        $filename = '';
        $up_stub_content = '';
        $down_stub_content = '';

        if ($type === 'create') {
            $className = 'Create' . str_replace('_', '', ucwords($name, '_')) . 'Table';
            $filename = "{$timestamp}_create_{$name}_table.php";
            $up_stub_content = <<<PHP
        \$this->createTable('{$name}', [
            col('id')->id(), // Auto-incrementing primary key
            // Add your table columns here, e.g:
            // col('column_name')->string(255)->notNullable()->unique(),
            // col('description')->text()->nullable(),
            // col('is_active')->integer()->default(0),
            // col('status')->enum(['pending', 'approved', 'rejected'])->default('pending'),
            timestamps(), // created_at and updated_at
        ]);
PHP;
            $down_stub_content = <<<PHP
        \$this->dropTable('{$name}');
PHP;
        } elseif ($type === 'drop') {
            $className = 'Drop' . str_replace('_', '', ucwords($name, '_')) . 'Table';
            $filename = "{$timestamp}_drop_{$name}_table.php";
            $up_stub_content = <<<PHP
        \$this->dropTable('{$name}');
PHP;
            $down_stub_content = <<<PHP
        // TODO: Define how to recreate the {$name} table here if you want to be able to rollback.
PHP;
        }

        $filepath = "{$directory}/{$filename}";
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
            echo "Error: Could not create migration file.\n";
            exit(1);
        }

        echo "Created Migration: {$filename}\n";
    }
}