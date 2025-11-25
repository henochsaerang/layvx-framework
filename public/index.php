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

$container = new Container();
App::setContainer($container);

// Register Service Providers
$providers = require __DIR__ . '/../config/app.php';
foreach ($providers['providers'] as $providerClass) {
    (new $providerClass($container))->register();
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
$request = $container->resolve(\App\Core\Request::class);

// Resolve and run the kernel
$kernel = new \App\Core\Kernel($container, $container->resolve(\App\Core\Router::class));
$response = $kernel->handleRequest($request);

// --- Send The Response ---
$response->send();

