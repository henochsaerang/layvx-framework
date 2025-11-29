<?php

namespace App\Commands;

use App\Core\Command;
use App\Core\StructureDefinitions;

class MakeStructureCommand extends Command
{
    protected string $description = 'Create a new project structure (mvc, adr, ddd, minimal).';

    public function handle(array $args)
    {
        $commandName = $args['command_name'] ?? '';
        $parts = explode(':', $commandName);
        $type = $parts[1] ?? null;

        if (!$type) {
            echo "Error: Tipe struktur tidak valid. Contoh: buat:mvc\n";
            return;
        }

        echo "Membuat struktur " . strtoupper($type) . "...\n";

        $directories = StructureDefinitions::getDirectories($type);

        if (empty($directories)) {
            echo "Error: Tipe preset '{$type}' tidak dikenal.\n";
            return;
        }

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                echo "Direktori dibuat: {$dir}\n";
            }
        }
        
        $this->createBoilerplateFiles($type);

        echo "\nStruktur " . strtoupper($type) . " berhasil dibuat.\n";
    }

    private function createBoilerplateFiles(string $type)
    {
        $this->createIndexFile();
        $this->createRoutesFile($type);
        $this->createAppServiceProvider();
    }

    private function createIndexFile()
    {
        $content = <<<PHP
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../routes/web.php';

use App\Core\App;

// Run the application
App::run();
PHP;
        file_put_contents('public/index.php', $content);
        echo "File dibuat: public/index.php\n";
    }

    private function createRoutesFile(string $type)
    {
        $content = "<?php\n\nuse App\Core\Route;\n\n";

        switch ($type) {
            case 'mvc':
                $content .= "Route::get('/', 'HomeController@index');\n";
                break;
            case 'adr':
                $content .= "Route::get('/', 'HomeAction');\n";
                break;
            case 'minimal':
            default:
                $content .= "Route::get('/', function () {\n    return 'Selamat datang di LayVX!';\n});\n";
                break;
        }

        file_put_contents('routes/web.php', $content);
        echo "File dibuat: routes/web.php\n";
    }

    private function createAppServiceProvider()
    {
        $content = <<<PHP
<?php

namespace App\Providers;

use App\Core\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        //
    }
}
PHP;
        file_put_contents('app/Providers/AppServiceProvider.php', $content);
        echo "File dibuat: app/Providers/AppServiceProvider.php\n";
    }
}