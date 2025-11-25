<?php

use App\Core\Route;
use App\Core\Response;
use App\Core\Session;

// Route utama aplikasi
Route::get('/', ['LandingController', 'index']);

// --- Tambahkan rute aplikasi Anda di sini ---
// Contoh:
// Route::get('/about', ['AboutController', 'index']);
// Route::post('/contact', ['ContactController', 'submit']);

// Contoh penggunaan Route Group:
// Route::group(['middleware' => 'auth'], function ($router) {
//     $router->get('/dashboard', ['DashboardController', 'index']);
// });
