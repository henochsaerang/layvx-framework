<?php

namespace App\Commands;

use App\Core\Command;
use App\Core\StructureDefinitions;

class MakeStructureCommand extends Command
{
    protected $description = 'Create a new project structure (mvc, adr, ddd, minimal).';

    public function handle(array $args = [])
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
        
        // Wajib membuat middleware karena Kernel.php mereferensikannya secara global
        $this->createCsrfMiddleware();
    }

    private function createCsrfMiddleware()
    {
        if (!is_dir('app/Middleware')) {
            mkdir('app/Middleware', 0755, true);
        }

        $content = <<<'PHP'
<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Session;
use App\Core\Response;
use Closure;

class VerifyCsrfToken implements Middleware
{
    public function handle(Request $request, Closure $next)
    {
        Session::start();

        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $next($request);
        }

        $token = $request->input('tuama_token');
        $sessionToken = Session::token();

        if (is_null($token) || is_null($sessionToken) || !hash_equals($sessionToken, $token)) {
            // Tampilkan halaman error 419 yang cantik
            return Response::view('errors.419', [], 419);
        }

        return $next($request);
    }
}
PHP;
        file_put_contents('app/Middleware/VerifyCsrfToken.php', $content);
        echo "File dibuat: app/Middleware/VerifyCsrfToken.php\n";
    }

    private function createIndexFile()
    {
        $content = <<<'PHP'
<?php

// public/index.php

use App\Core\App;
use App\Core\Container;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;

// 1. Load Environment & Autoloader Internal
require_once '../app/Core/Env.php';
\App\Core\Env::load(__DIR__ . '/..');

require_once '../app/Core/autoloader.php';

// 2. Setup Container
$container = new Container();
App::setContainer($container);

// 3. Load Providers
$providers = require __DIR__ . '/../config/app.php';
if (isset($providers['providers'])) {
    foreach ($providers['providers'] as $providerClass) {
        if (class_exists($providerClass)) {
            (new $providerClass($container))->register();
        }
    }
}

// 4. Register Handlers & Helpers
require_once '../app/Core/ErrorHandler.php';
\App\Core\ErrorHandler::register();

require_once '../app/Core/helpers.php';

// 5. Start Session
Session::start();
if (!Session::has('tuama_token')) {
    Session::regenerateToken();
}

// 6. Handle Request
\App\Core\Route::load('../routes/web.php');

$request = $container->resolve(Request::class);
$kernel = new \App\Core\Kernel($container, $container->resolve(Router::class));
$response = $kernel->handleRequest($request);

$response->send();
PHP;

        file_put_contents('public/index.php', $content);
        echo "File dibuat: public/index.php (Zero Dependency Mode)\n";
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
        $content = <<<'PHP'
<?php

namespace App\Providers;

use App\Core\ServiceProvider;
use App\Core\Container;
use App\Core\Request;
use App\Core\Router;
use PDO;
use PDOException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // 1. Bind Router & Request
        $this->container->singleton(Router::class, function (Container $container) {
            return new Router($container->resolve(Request::class));
        });

        $this->container->singleton(Request::class, function () {
            return Request::capture();
        });

        // 2. Bind Database Connection
        $this->container->singleton(PDO::class, function () {
            $driver = config('database.driver', 'mysql');
            $host = config('database.host', $_ENV['DB_HOST'] ?? '127.0.0.1');
            $dbName = config('database.db_name', 'layvx_db');
            $dbUser = config('database.db_user', 'root');
            $dbPass = config('database.db_pass', '');
            
            $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";

            try {
                $pdo = new PDO($dsn, $dbUser, $dbPass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                return $pdo;
            } catch (PDOException $e) {
                throw $e;
            }
        });
    }
}
PHP;
        file_put_contents('app/Providers/AppServiceProvider.php', $content);
        echo "File dibuat: app/Providers/AppServiceProvider.php (Database Ready)\n";
    }
}