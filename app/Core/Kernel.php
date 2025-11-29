<?php

namespace App\Core;

use App\Commands\CacheClearCommand;
use App\Commands\HelpCommand;
use App\Commands\MakeControllerCommand;
use App\Commands\MakeMigrationCommand;
use App\Commands\MakeModelCommand;
use App\Commands\MigrateCommand;
use App\Commands\ServeCommand;
use App\Commands\MakeMiddlewareCommand;
use App\Commands\MakeViewCommand;
use App\Commands\MakeStructureCommand;
use App\Commands\DeleteStructureCommand;
use App\Commands\MakeModuleCommand;
use App\Commands\MakeExeCommand;
use App\Commands\DeleteExeCommand;
use App\Commands\MakePwaCommand;

class Kernel {
    /** The application's global HTTP middleware stack. 
     * Hanya daftarkan middleware yang PASTI ada. 
     * Jika file App\Middleware\VerifyCsrfToken.php dihapus, daftar ini harus kosong
     * atau file VerifyCsrfToken harus dibuat ulang menggunakan `layvx buat:middleware VerifyCsrfToken`.
     */
    protected $globalMiddleware = [
        // \App\Middleware\VerifyCsrfToken::class, // Dihapus karena filenya mungkin hilang
    ];

    /** The application's route middleware groups. */
    protected $routeMiddleware = [
        //
    ];

    /** The registered console commands. */
    protected $commands = [
        'help' => HelpCommand::class,
        'serve' => ServeCommand::class,
        'cache:clear' => CacheClearCommand::class,
        'migrasi' => MigrateCommand::class,
        
        // Make commands
        'buat:controller' => MakeControllerCommand::class,
        'buat:model' => MakeModelCommand::class,
        'buat:middleware' => MakeMiddlewareCommand::class,
        'buat:view' => MakeViewCommand::class,
        'buat:tabel' => MakeMigrationCommand::class,
        'buat:hapus_tabel' => MakeMigrationCommand::class,
        'buat:modul' => MakeModuleCommand::class,
        'buat:exe' => MakeExeCommand::class,
        'buat:pwa' => MakePwaCommand::class,
        
        // Dynamic Structure Scaffolding
        'buat:mvc' => MakeStructureCommand::class,
        'buat:adr' => MakeStructureCommand::class,
        'buat:ddd' => MakeStructureCommand::class,
        'buat:hmvc' => MakeStructureCommand::class,
        'buat:minimal' => MakeStructureCommand::class,
        'buat:hapus_mvc' => \App\Commands\DeleteStructureCommand::class,
        'buat:hapus_adr' => \App\Commands\DeleteStructureCommand::class,
        'buat:hapus_minimal' => \App\Commands\DeleteStructureCommand::class,
        'buat:hapus_ddd' => \App\Commands\DeleteStructureCommand::class,
        'buat:hapus_hmvc' => \App\Commands\DeleteStructureCommand::class,
        'buat:hapus_exe' => DeleteExeCommand::class,
    ];

    /** The application container. */
    protected $container;

    /** The router instance. */
    protected $router;

    public function __construct(Container $container, Router $router) {
        $this->container = $container;
        $this->router = $router;
    }

    /**
     * Handle an incoming HTTP request.
     *
     * @param Request $request
     * @return Response
     */
    public function handleRequest(Request $request): Response {
        try {
            $routeInfo = $this->router->dispatch();

            if (is_null($routeInfo)) {
                return Response::view('errors.404', [], 404);
            }

            // The final destination for the request after passing through all middleware.
            $destination = function ($request) use ($routeInfo) {
                return $this->router->callAction($routeInfo['action'], $routeInfo['params']);
            };

            $middlewares = $this->resolveMiddleware($routeInfo['middleware']);

            // Build the pipeline by wrapping the destination in middleware layers.
            $pipeline = array_reduce(
                array_reverse($middlewares),
                function (\Closure $next, string $middlewareClass) {
                    // Return a new closure that calls the middleware and passes the previous closure as \$next.
                    return function (Request $request) use ($next, $middlewareClass) {
                        return $this->container->resolve($middlewareClass)->handle($request, $next);
                    };
                },
                $destination // The initial "next" is the destination itself.
            );
            
            // Execute the fully-formed pipeline.
            $response = $pipeline($request);

            return $this->prepareResponse($response);

        } catch (\Exception $e) {
            // In a real app, this should use a registered exception handler.
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            return Response::view('errors.500', ['exception' => $e], 500);
        }
    }

    /**
     * Resolve middleware aliases to their class names.
     *
     * @param array $middlewares
     * @return array
     */
    protected function resolveMiddleware(array $middlewares): array {
        $resolved = $this->globalMiddleware;
        foreach ($middlewares as $middleware) {
            $resolved[] = $this->routeMiddleware[$middleware] ?? $middleware;
        }
        return array_unique($resolved);
    }
    
    /**
     * Ensure the response is a proper Response object.
     *
     * @param mixed $response
     * @return Response
     */
    protected function prepareResponse($response): Response {
        if (!$response instanceof Response) {
            return new Response($response);
        }
        return $response;
    }

    /**
     * Handle a console command.
     *
     * @param array $argv
     * @return void
     */
    public function handleConsole($argv) {
        $commandName = $argv[1] ?? 'help';
        $rawArgs = array_slice($argv, 2);
    
        $args = [];
        foreach ($rawArgs as $arg) {
            if (strpos($arg, '=') !== false) {
                list($key, $value) = explode('=', $arg, 2);
                $args[$key] = $value;
            } else {
                $args[] = $arg;
            }
        }
    
        if (!isset($this->commands[$commandName])) {
            echo "Error: Command '{$commandName}' not found.\n";
            (new HelpCommand($this))->handle([]);
            exit(1);
        }
    
        $commandClass = $this->commands[$commandName];
        $commandInstance = new $commandClass($this);
    
        // Inject the command name into the arguments array so the handler knows which command was called.
        $args['command_name'] = $commandName;
    
        $commandInstance->handle($args);
    }

    public function getCommands() {
        return $this->commands;
    }
}