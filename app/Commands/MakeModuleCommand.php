<?php

namespace App\Commands;

use App\Core\Command;

class MakeModuleCommand extends Command
{
    protected $description = 'Create a new HMVC module with its directory structure and a sample controller.';

    public function handle(array $args = [])
    {
        if (empty($args[0])) {
            echo "Error: Nama modul wajib diisi.\n";
            echo "Contoh: layvx buat:modul Blog\n";
            return;
        }

        $moduleName = ucfirst($args[0]);
        $basePath = "app/Modules/{$moduleName}";

        if (is_dir($basePath)) {
            echo "Error: Modul '{$moduleName}' sudah ada.\n";
            return;
        }

        echo "Membuat modul {$moduleName}...\n";

        // 1. Create module directories
        $directories = [
            "{$basePath}/Controllers",
            "{$basePath}/Models",
            "{$basePath}/Views",
        ];

        foreach ($directories as $dir) {
            mkdir($dir, 0755, true);
            echo "Direktori dibuat: {$dir}\n";
        }

        // 2. Create sample controller
        $this->createModuleController($moduleName, $basePath);

        echo "\nModul {$moduleName} berhasil dibuat! Jangan lupa daftarkan routenya.\n";
    }

    private function createModuleController(string $moduleName, string $basePath)
    {
        $controllerName = "{$moduleName}Controller";
        $filePath = "{$basePath}/Controllers/{$controllerName}.php";

        $content = <<<PHP
<?php

namespace App\\Modules\\{$moduleName}\\Controllers;

use App\\Core\\Controller;

class {$controllerName} extends Controller
{
    public function index()
    {
        return "Hello from {$moduleName} Module";
    }
}
PHP;

        file_put_contents($filePath, $content);
        echo "File controller dibuat: {$filePath}\n";
    }
}

