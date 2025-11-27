<?php

namespace App\Commands;

use App\Core\Command;

class MakeMvcCommand extends Command {
    protected $signature = 'buat:mvc';
    protected $description = 'Membuat semua direktori struktur MVC (Controllers, Models, Routes, Views, Database, Public) dan file bootstrap.';

    public function handle(array $args = []) {
        $basePath = __DIR__ . '/../../';
        
        $directories = [
            'app/Controllers',
            'app/Models',
            'app/Middleware',
            'app/Providers', // Pastikan folder Providers dibuat
            'database/tabel',
            'routes',
            'public',
            'views',
            'storage/framework/views',
            'public/assets/css',
            'public/uploads',
        ];

        echo "Creating LayVX directory structure...\n";
        $successCount = 0;

        foreach ($directories as $dir) {
            $fullPath = $basePath . $dir;
            if (!is_dir($fullPath)) {
                if (mkdir($fullPath, 0755, true)) {
                    echo "CREATED: {$dir}\n";
                    $successCount++;
                } else {
                    echo "ERROR: Failed to create {$dir}\n";
                }
            }
        }
        
        // --- Membuat File Dasar yang Penting ---
        
        // 1. routes/web.php
        $routeFile = $basePath . 'routes/web.php';
        if (!file_exists($routeFile)) {
            $this->createRouteFile($routeFile);
        }
        
        // 2. public/index.php
        $indexFile = $basePath . 'public/index.php';
        if (!file_exists($indexFile)) {
            $this->createIndexFile($indexFile);
        }
        
        // 3. public/.htaccess
        $htaccessFile = $basePath . 'public/.htaccess';
        if (!file_exists($htaccessFile)) {
            $this->createHtaccessFile($htaccessFile);
        }

        // 4. views/welcome.php
        $welcomeView = $basePath . 'views/welcome.php';
        if (!file_exists($welcomeView)) {
            $this->createWelcomeView($welcomeView);
        }

        // 5. app/Providers/AppServiceProvider.php
        $providerFile = $basePath . 'app/Providers/AppServiceProvider.php';
        if (!file_exists($providerFile)) {
            $this->createAppServiceProvider($providerFile);
        }

        echo "\nStructure creation completed. {$successCount} directories created.\n";
    }

    private function createRouteFile(string $filepath) {
        $stub = <<<PHP
<?php

use App\Core\Route;
use App\Core\Response;

// Default Route
Route::get('/', function () {
    return Response::view('welcome');
});

// Example Controller Route
// Route::get('/home', ['HomeController', 'index']);
PHP;
        file_put_contents($filepath, $stub);
        echo "CREATED: routes/web.php\n";
    }

    private function createIndexFile(string $filepath) {
        $stub = <<<PHP
<?php

// public/index.php

use App\Core\App;
use App\Core\Container;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Route;

// --- Bootstrap The Application ---

require_once '../app/Core/Env.php';
\App\Core\Env::load(__DIR__ . '/..');

require_once '../app/Core/autoloader.php';

// --- Service Container & Providers ---

\$container = new Container();
App::setContainer(\$container);

// Register Service Providers (Make sure AppServiceProvider is configured in config/app.php)
\$providers = require __DIR__ . '/../config/app.php';
if (isset(\$providers['providers'])) {
    foreach (\$providers['providers'] as \$providerClass) {
        (new \$providerClass(\$container))->register();
    }
}

// --- Register Handlers & Helpers ---

require_once '../app/Core/ErrorHandler.php';
\App\Core\ErrorHandler::register();

require_once '../app/Core/helpers.php';

use App\Core\Session;

// Start the session and initialize CSRF token if not present
Session::start();
if (!Session::has('tuama_token')) {
    Session::regenerateToken();
}

// --- Handle The Request ---

// Load route definitions
\App\Core\Route::load('../routes/web.php');

// Resolve the request from the container
\$request = \$container->resolve(\App\Core\Request::class);

// Resolve and run the kernel
\$kernel = new \App\Core\Kernel(\$container, \$container->resolve(\App\Core\Router::class));
\$response = \$kernel->handleRequest(\$request);

// --- Send The Response ---
\$response->send();
PHP;
        file_put_contents($filepath, $stub);
        echo "CREATED: public/index.php\n";
    }
    
    private function createHtaccessFile(string $filepath) {
        $stub = <<<APACHE
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Handle Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
APACHE;
        file_put_contents($filepath, $stub);
        echo "CREATED: public/.htaccess\n";
    }

    private function createWelcomeView(string $filepath) {
        $stub = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LayVX Framework</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="max-w-xl mx-auto p-8 bg-white shadow-xl rounded-lg text-center">
        <h1 class="text-4xl font-extrabold text-blue-700 mb-4">LayVX Framework</h1>
        <p class="text-lg text-gray-600 mb-6">Aplikasi berhasil di-setup! Anda siap untuk mengembangkan.</p>
        <div class="space-y-3">
            <p class="text-sm text-gray-500">
                Untuk memulai, edit file 
                <code class="bg-gray-200 px-2 py-1 rounded text-red-600">routes/web.php</code> 
                atau buat Controller dan Model baru.
            </p>
            <p>
                <code class="bg-gray-200 px-2 py-1 rounded text-green-700">Perintah CLI Anda sekarang sudah aktif.</code>
            </p>
        </div>
        <div class="mt-8 pt-4 border-t border-gray-200">
            <a href="https://layvx.github.io" target="_blank" class="text-blue-500 hover:text-blue-600 font-medium">Baca Dokumentasi</a>
        </div>
    </div>
</body>
</html>
HTML;
        file_put_contents($filepath, $stub);
        echo "CREATED: views/welcome.php\n";
    }

    private function createAppServiceProvider(string $filepath) {
        // Konten AppServiceProvider dari versi sebelumnya (yang mendukung PDO)
        $stub = <<<PHP
<?php

namespace App\Providers;

use App\Core\ServiceProvider;
use App\Core\Container;
use App\Core\Request;
use App\Core\Router;
use PDO;
use PDOException;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        // Bind the Router, it depends on the Request
        \$this->container->singleton(Router::class, function (Container \$container) {
            return new Router(\$container->resolve(Request::class));
        });

        // The Request is captured once and treated as a singleton for the lifecycle.
        \$this->container->singleton(Request::class, function () {
            return Request::capture();
        });

        // Bind the database connection as a singleton
        \$this->container->singleton(PDO::class, function () {
            // Use the new config helper
            \$driver = config('database.driver', 'mysql');
            \$host = config('database.host', \$_ENV['DB_HOST'] ?? null);
            \$dbName = config('database.db_name', 'layvx_db');
            \$dbUser = config('database.db_user', 'root');
            \$dbPass = config('database.db_pass', '');
            \$port = config('database.port', '3306');
            \$charset = config('database.charset', 'utf8mb4');
            
            // Logika koneksi PDO
            \$dsn = '';

            switch (\$driver) {
                case 'mysql':
                    \$dsn = "mysql:host={\$host};dbname={\$dbName};charset={\$charset}";
                    break;
                case 'pgsql':
                    \$dsn = "pgsql:host={\$host};port={\$port};dbname={\$dbName}";
                    break;
                default:
                    throw new \Exception("Unsupported database driver: {\$driver}");
            }

            try {
                \$pdo = new PDO(\$dsn, \$dbUser, \$dbPass);
                \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                return \$pdo;
            } catch (PDOException \$e) {
                // Di lingkungan CLI, ini akan teratasi oleh error handler, 
                // tetapi di HTTP, ia akan mati jika koneksi gagal.
                // Logika ini memastikan PDO hanya dibuat jika diperlukan dan konfigurasinya valid.
                throw \$e;
            }
        });
    }
}
PHP;
        file_put_contents($filepath, $stub);
        echo "CREATED: app/Providers/AppServiceProvider.php\n";
    }
}