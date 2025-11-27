<?php

namespace App\Core;

use App\Commands\CacheClearCommand;
use App\Commands\HelpCommand;
use App\Commands\MakeControllerCommand;
use App\Commands\MakeMigrationCommand;
use App\Commands\MakeModelCommand;
use App\Commands\MigrateCommand;
use App\Commands\ServeCommand;
use App\Commands\MakeMvcCommand;
use App\Commands\DeleteMvcCommand;
use App\Commands\ResetMvcCommand;
use App\Commands\MakeMiddlewareCommand;
use App\Commands\MakeViewCommand;

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
        'serve' => ServeCommand::class,
        'cache:clear' => CacheClearCommand::class,
        'buat:controller' => MakeControllerCommand::class,
        'buat:model' => MakeModelCommand::class,
        'buat:middleware' => MakeMiddlewareCommand::class,
        'buat:view' => MakeViewCommand::class,
        'buat:tabel' => MakeMigrationCommand::class,
        'buat:hapus_tabel' => MakeMigrationCommand::class,
        'migrasi' => MigrateCommand::class,
        'help' => HelpCommand::class,
        'buat:mvc' => MakeMvcCommand::class,
        'buat:hapus_mvc' => DeleteMvcCommand::class,
        'buat:reset_mvc' => ResetMvcCommand::class,
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
        $args = array_slice($argv, 2);

        if (!isset($this->commands[$commandName])) {
            echo "Error: Command '{$commandName}' not found.\n";
            (new HelpCommand())->handle();
            exit(1);
        }

        $commandClass = $this->commands[$commandName];
        $commandInstance = new $commandClass();

        // A bit of a hack to pass the command name to the migration creator
        if ($commandName === 'buat:hapus_tabel') {
            $args['command'] = 'buat:hapus_tabel';
        }

        $commandInstance->handle($args);
    }

    public function getCommands() {
        return $this->commands;
    }
}