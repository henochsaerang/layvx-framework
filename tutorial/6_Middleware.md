# Tutorial Framework: Middleware

Middleware menyediakan mekanisme untuk memfilter HTTP request yang masuk ke aplikasi Anda. Middleware bertindak sebagai "lapisan" yang harus dilewati oleh request sebelum mencapai Controller atau route. Contohnya, middleware bisa digunakan untuk memeriksa apakah pengguna sudah login, atau untuk memverifikasi token CSRF.

## 1. Membuat Middleware

Sebuah middleware adalah sebuah class yang mengimplementasikan interface `App\Core\Middleware`. Class ini harus memiliki satu method public bernama `handle`.

**Struktur Dasar:**
Middleware disimpan di direktori `app/Middleware`.
```php
<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use Closure;

class ContohMiddleware implements Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \App\Core\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Lakukan sesuatu SEBELUM request mencapai controller
        // Contoh: Mencatat log, memeriksa header, dll.

        // Jika middleware ini tidak mengizinkan request lanjut,
        // Anda bisa mengembalikan response dari sini.
        // if ($syarat_tidak_terpenuhi) {
        //     return Response::redirect('/login');
        // }

        // Meneruskan request ke lapisan selanjutnya (middleware lain atau controller)
        $response = $next($request);

        // Lakukan sesuatu SETELAH controller selesai memproses request
        // dan sudah ada response yang dihasilkan.

        return $response;
    }
}
```
-   `$request`: Objek request yang sedang diproses.
-   `$next`: Sebuah Closure yang harus dipanggil untuk meneruskan request ke lapisan berikutnya. Jika `$next($request)` tidak dipanggil, maka request akan berhenti di middleware ini.

**Contoh Praktis: Middleware Pengecek Role**
Misalkan kita ingin membuat middleware yang hanya mengizinkan user dengan role 'dosen' untuk mengakses suatu halaman.

`app/Middleware/DosenMiddleware.php`:
```php
<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Session;
use App\Core\Response;
use Closure;

class DosenMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next)
    {
        // Asumsikan data user yang login disimpan di session
        $user = Session::get('user');

        if (!$user || $user->role !== 'dosen') {
            // Jika tidak ada user atau role bukan 'dosen',
            // kembalikan ke halaman lain atau tampilkan error.
            return Response::redirect('/');
        }

        // Jika user adalah dosen, lanjutkan request.
        return $next($request);
    }
}
```

## 2. Registrasi Middleware

Setelah dibuat, middleware harus diregistrasi di `app/Core/Kernel.php` agar bisa digunakan.

Ada dua jenis registrasi:

### a. Global Middleware
Middleware ini akan dijalankan pada **setiap** HTTP request.
```php
// app/Core/Kernel.php

class Kernel {
    /** The application's global HTTP middleware stack. */
    protected $globalMiddleware = [
        \App\Middleware\VerifyCsrfToken::class,
        // Tambahkan middleware global lain di sini
    ];
    // ...
}
```

### b. Route Middleware (Alias)
Middleware ini diberi sebuah "alias" (nama pendek) dan bisa diterapkan ke route atau grup route tertentu.
```php
// app/Core/Kernel.php

class Kernel {
    // ...
    /** The application's route middleware groups. */
    protected $routeMiddleware = [
        'auth' => \App\Middleware\AuthMiddleware::class,
        'dosen' => \App\Middleware\DosenMiddleware::class,
        'mahasiswa' => \App\Middleware\MahasiswaMiddleware::class,
        // Registrasikan DosenMiddleware dengan alias 'dosen'
    ];
    // ...
}
```
Di sini, kita memberi alias `'dosen'` untuk `\App\Middleware\DosenMiddleware::class`.

## 3. Menggunakan Middleware pada Route

Setelah diregistrasi dengan alias, Anda bisa menggunakannya di `routes/web.php`.

```php
// routes/web.php
use App\Core\Route;

// Halaman dashboard dosen, hanya bisa diakses oleh user
// yang lolos dari middleware 'dosen'.
Route::get('/dosen/dashboard', ['DosenController', 'dashboard'], ['middleware' => 'dosen']);

// Anda juga bisa menerapkan middleware pada grup route
Route::group(['middleware' => 'dosen'], function () {

    Route::get('/dosen/matakuliah', ['DosenController', 'listMatakuliah']);
    Route::get('/dosen/matakuliah/{id}', ['DosenController', 'detailMatakuliah']);
    // Semua route di dalam grup ini akan dilindungi oleh DosenMiddleware

});
```
Jika sebuah route membutuhkan beberapa middleware, Anda bisa mendefinisikannya dalam bentuk array: `['middleware' => ['auth', 'dosen']]`.
