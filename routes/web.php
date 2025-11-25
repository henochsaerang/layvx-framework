<?php

use App\Core\Route;
use App\Core\Response;
use App\Core\Session;

Route::get('/', ['LandingController', 'index']);

// --- Admin Routes ---
Route::group(['middleware' => 'auth.admin'], function ($router) {
    $router->get('/admin/dashboard', ['DashboardController', 'index']);
});

Route::get('/login-admin', function () {
    return new Response('Halaman login untuk Admin.', 401);
});

Route::get('/test-login-admin', function () {
    Session::set('user_id', 1); // Admin ID
    return Response::redirect('/admin/dashboard');
});

// --- Karyawan (Employee) Routes ---
Route::group(['middleware' => 'auth.karyawan'], function ($router) {
    $router->get('/karyawan/dashboard', ['KaryawanDashboardController', 'index']);
});

Route::get('/login-karyawan', function () {
    return new Response('Halaman login untuk Karyawan.', 401);
});

Route::get('/test-login-karyawan', function () {
    Session::set('karyawan_id', 1); // Karyawan ID
    return Response::redirect('/karyawan/dashboard');
});


// --- Global Logout (example) ---
Route::get('/logout', function () {
    Session::forget('user_id');
    Session::forget('karyawan_id');
    return Response::redirect('/');
});
