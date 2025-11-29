<?php

namespace App\Commands;

use App\Core\Command;

class MakeControllerCommand extends Command {
    protected $signature = 'buat:controller';
    protected $description = 'Membuat class Controller baru.';

    public function handle(array $args = []) {
        $controllerName = $args[0] ?? null;
        if (empty($controllerName)) {
            echo "Error: Please provide a name for the controller.\n";
            echo "Usage: layvx buat:controller <ControllerName>\n";
            exit(1);
        }

        // Ensure "Controller" suffix and PascalCase
        $controllerName = ucfirst($controllerName) . 'Controller';
        
        // Define paths
        $basePath = __DIR__ . '/../../'; // From app/Commands to project root
        $directory = $basePath . 'app/Controllers';
        $filepath = $directory . '/' . $controllerName . '.php';

        // Check if directory exists, create if not
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                echo "Error: Failed to create directory {$directory}.\n";
                exit(1);
            }
            echo "Directory created: {$directory}\n";
        }

        // Check if file already exists
        if (file_exists($filepath)) {
            echo "Error: Controller {$controllerName} already exists.\n";
            exit(1);
        }

        $stub = <<<PHP
<?php

namespace App\Controllers;

class {$controllerName} {
    public function index() {
        // Default method
        echo "Hello from {$controllerName}!";
    }
}

PHP;

        if (file_put_contents($filepath, $stub) === false) {
            echo "Error: Could not create controller file.\n";
            exit(1);
        }
        echo "Controller created successfully: {$filepath}\n";
    }
}