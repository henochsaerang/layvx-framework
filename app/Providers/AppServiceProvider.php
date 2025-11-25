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
        $this->container->singleton(Router::class, function (Container $container) {
            return new Router($container->resolve(Request::class));
        });

        // The Request is captured once and treated as a singleton for the lifecycle.
        $this->container->singleton(Request::class, function () {
            return Request::capture();
        });

        // Bind the database connection as a singleton
        $this->container->singleton(PDO::class, function () {
            // Use the new config helper
            $driver = config('database.driver', 'mysql');
            $host = config('database.host', '127.0.0.1');
            $dbName = config('database.db_name', 'layvx_db');
            $dbUser = config('database.db_user', 'root');
            $dbPass = config('database.db_pass', '');
            $port = config('database.port', '3306');
            $charset = config('database.charset', 'utf8mb4');
            
            $dsn = '';

            switch ($driver) {
                case 'mysql':
                    $dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";
                    break;
                case 'pgsql':
                    $dsn = "pgsql:host={$host};port={$port};dbname={$dbName}";
                    break;
                // Add other drivers like sqlite here
                default:
                    throw new \Exception("Unsupported database driver: {$driver}");
            }

            try {
                $pdo = new PDO($dsn, $dbUser, $dbPass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                return $pdo;
            } catch (PDOException $e) {
                // In a real app, this should be handled by the exception handler
                // to show a proper error page instead of dying.
                die("Database connection failed: " . $e->getMessage());
            }
        });
    }
}
