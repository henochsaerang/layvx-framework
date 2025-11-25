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

// --- Service Container Setup ---

$container = new Container();
App::setContainer($container);

// Capture the request early
$request = Request::capture();
$container->singleton(Request::class, fn() => $request);

// Bind the Router, it depends on the Request
$container->singleton(Router::class, function () use ($request) {
    return new Router($request);
});

// Bind the database connection
$container->singleton(PDO::class, function () {
    $config = require __DIR__ . '/../config/database.php';
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['db_name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Koneksi Gagal: " . $e->getMessage());
    }
});

// --- Register Handlers & Helpers ---

require_once '../app/Core/ErrorHandler.php';
\App\Core\ErrorHandler::register();

require_once '../app/Core/helpers.php';

session_start();
if (!isset($_SESSION['tuama_token'])) {
    $_SESSION['tuama_token'] = bin2hex(random_bytes(32));
}

// --- Dispatch The Request ---

// Load route definitions from routes/web.php
Route::load('../routes/web.php');

// Dispatch the router to get a response
$response = app(Router::class)->dispatch();

// --- Send The Response ---

if ($response instanceof Response) {
    $response->send();
} elseif ($response) {
    (new Response($response))->send();
} else {
    // This case should not be reached if the router is working correctly,
    // as it handles its own 404 responses.
    (new Response('Not Found', 404))->send();
}
