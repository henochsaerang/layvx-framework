# Dokumentasi LayVX Framework

Dokumen ini menjelaskan arsitektur modern dan fitur-fitur utama dari LayVX Framework, termasuk pembaruan besar pada arsitektur Middleware Pipeline, Service Provider, Manajemen Sesi, dan Route Grouping.

## 1. Konfigurasi

### a. Environment (`.env`)
File `.env` adalah tempat Anda menyimpan semua variabel konfigurasi yang spesifik untuk lingkungan (lokal, produksi).

**Contoh `.env`:**
```env
APP_ENV=development

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=layvx_db
DB_USERNAME=root
DB_PASSWORD=
```

### b. File Konfigurasi (`config/`)
Folder `config/` berisi file-file yang membaca variabel dari `.env`.

Framework ini menggunakan helper global `config()` untuk mengakses nilai-nilai konfigurasi, termasuk menggunakan *dot notation* untuk mengakses array bersarang (misalnya, `config('app.env')`).

```php
// config/app.php (Contoh)
return [
    'name' => 'LayVX Framework',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'providers' => [
        // ...
    ],
];
```
Anda dapat mengakses nilai `env` dari `app.php` dengan `config('app.env')`.

---

## 2. Arsitektur Inti & Alur Request

### a. Service Container & Auto-Wiring
Service Container (`App\Core\Container`) adalah inti dari manajemen dependensi framework. Ia memungkinkan Anda untuk:

*   **Auto-Wiring (Injeksi Dependensi Otomatis)**: Untuk sebagian besar kelas, container akan secara otomatis menginjeksi dependensi ke dalam konstruktor atau metode controller Anda berdasarkan *type-hinting* (menggunakan PHP Reflection API).
    ```php
    class ContohController {
        // Container akan otomatis menyediakan instance Request
        public function __construct(App\Core\Request $request) {
            $this->request = $request;
        }
    }
    ```
*   **Manual Binding**: Untuk service yang lebih kompleks (misalnya, perlu logika khusus untuk pembuatan instance, atau perlu dipastikan hanya ada satu instance/singleton), Anda dapat mendaftarkannya secara manual. Ini dilakukan di **Service Provider**.

### b. Service Provider
**Service Provider** adalah tempat utama untuk mendaftarkan service dan *binding* ke *Service Container*. Ini dilakukan di dalam `App\Providers\AppServiceProvider.php` dan service provider lainnya yang Anda buat.

```php
// app/Providers/AppServiceProvider.php
class AppServiceProvider extends ServiceProvider {
    public function register() {
        // Contoh binding manual untuk Router (singleton, butuh Request di konstruktor)
        $this->container->singleton(Router::class, function (Container $container) {
            return new Router($container->resolve(Request::class));
        });
        
        // Contoh binding manual untuk koneksi database (PDO)
        $this->container->singleton(PDO::class, function () {
            // Menggunakan helper config() baru untuk membaca konfigurasi database
            $driver = config('database.driver', 'mysql');
            $host = config('database.host', '127.0.0.1');
            // ... dst
            return new PDO($dsn, $dbUser, $dbPass);
        });
    }
}
```
Service Provider didaftarkan di `config/app.php` untuk di-load secara otomatis.

### c. Siklus Hidup Request (Middleware Pipeline)
Framework ini mengadopsi arsitektur **Middleware Pipeline** yang modern, sering dianalogikan sebagai "lapisan bawang" (onion).

1.  **Entry Point**: Semua request masuk melalui `public/index.php`. File ini hanya melakukan setup awal dan memulai sesi.
2.  **Kernel**: Request kemudian diserahkan ke `App\Core\Kernel`. Kernel adalah "jantung" dari aplikasi HTTP Anda yang mengelola pipeline middleware.
3.  **Middleware Pipeline**: Kernel membangun sebuah "pipeline" yang terdiri dari serangkaian middleware.
4.  **Perjalanan Request**: Request "mengalir" masuk melewati setiap lapisan middleware (misalnya, `VerifyCsrfToken` -> `AuthAdminMiddleware`). Setiap middleware dapat memeriksa atau memodifikasi request sebelum meneruskannya ke lapisan selanjutnya.
5.  **Injeksi Parameter Rute**: Saat request mencapai Controller, `Router` akan secara otomatis menginjeksi parameter rute dari URI (misalnya, nilai `{id}` dari `/posts/{id}`) ke dalam parameter metode Controller yang sesuai.
6.  **Tujuan (Controller)**: Jika request berhasil melewati semua middleware, ia akan mencapai tujuannya, yaitu metode Controller.
7.  **Perjalanan Response**: Response dari Controller kemudian "mengalir" kembali keluar melewati lapisan-lapisan middleware yang sama, memberikan kesempatan bagi middleware untuk memodifikasi response sebelum dikirim ke user.

---

## 3. Middleware

Middleware adalah "penjaga" yang menyaring request HTTP. Contoh: memverifikasi otentikasi, mencatat log, dll.

### a. Membuat Middleware
Sebuah middleware adalah class yang mengimplementasikan `App\Core\Middleware` dan memiliki metode `handle`.

```php
// app/Middleware/ContohMiddleware.php
namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use Closure;

class ContohMiddleware implements Middleware 
{
    public function handle(Request $request, Closure $next) 
    {
        // Lakukan sesuatu SEBELUM request mencapai controller...

        // Teruskan request ke lapisan selanjutnya di pipeline
        $response = $next($request);

        // Lakukan sesuatu SETELAH controller menghasilkan response...

        return $response;
    }
}
```

### b. Mendaftarkan Middleware
Middleware didaftarkan di `app/Core/Kernel.php`.
- **Global Middleware**: Dijalankan pada setiap request.
  ```php
  protected $globalMiddleware = [
      \App\Middleware\VerifyCsrfToken::class,
  ];
  ```
- **Route Middleware**: Diberi "nama alias" dan dapat diterapkan ke rute tertentu. Ini sangat berguna untuk otentikasi.
  ```php
  protected $routeMiddleware = [
      'auth.admin' => \App\Middleware\AuthAdminMiddleware::class,
      'auth.karyawan' => \App\Middleware\AuthKaryawanMiddleware::class,
  ];
  ```

---

## 4. Sistem Routing (`routes/web.php`)
Sistem routing memetakan URL ke metode Controller.

### a. Mendefinisikan Rute
Gunakan facade `App\Core\Route` di `routes/web.php`.

```php
// routes/web.php
use App\Core\Route;

// Rute statis
Route::get('/', ['LandingController', 'index']);

// Rute Dinamis dengan parameter {id}
// Parameter rute ($id) akan otomatis diinjeksi ke metode controller.
Route::get('/posts/{id}', ['PostController', 'show']);
```

### b. Route Grouping (Mengelompokkan Rute)
Anda dapat mengelompokkan rute yang memiliki atribut yang sama (misalnya, middleware yang sama) menggunakan metode `Route::group()`. Ini membuat file rute Anda lebih rapi dan menghindari pengulangan kode, serta bersifat **stateless** (tidak perlu mengingat rute terakhir yang didaftarkan).

```php
// routes/web.php
use App\Core\Route;

// Grup Rute Admin dengan middleware 'auth.admin'
Route::group(['middleware' => 'auth.admin'], function ($router) {
    $router->get('/admin/dashboard', ['DashboardController', 'index']);
    $router->get('/admin/settings', ['AdminController', 'settings']);
});

// Grup Rute Karyawan dengan middleware 'auth.karyawan'
Route::group(['middleware' => 'auth.karyawan'], function ($router) {
    $router->get('/karyawan/dashboard', ['KaryawanDashboardController', 'index']);
});
```
*Catatan: Metode `Route::middleware()` yang stateful kini sudah tidak digunakan dan telah digantikan oleh `Route::group()`.*

---

## 5. Manajemen Sesi (`App\Core\Session`)

Untuk pengelolaan sesi yang lebih bersih, aman, dan fleksibel, framework ini menyediakan class `App\Core\Session`. Anda **tidak disarankan** lagi untuk mengakses `$_SESSION` secara langsung.

**Metode Utama `App\Core\Session`:**
*   `Session::start()`: Memulai sesi PHP (sudah dipanggil secara otomatis di `public/index.php`).
*   `Session::set(string $key, mixed $value)`: Mengatur nilai dalam sesi.
*   `Session::get(string $key, mixed $default = null)`: Mengambil nilai dari sesi, dengan nilai default opsional.
*   `Session::has(string $key)`: Memeriksa apakah kunci sesi ada.
*   `Session::forget(string $key)`: Menghapus satu kunci dari sesi.
*   `Session::token()`: Mengambil token CSRF.
*   `Session::regenerateToken()`: Membuat ulang token CSRF (dilakukan secara otomatis di `public/index.php` jika token belum ada).

**Contoh Penggunaan:**

```php
use App\Core\Session;

// Mengatur nilai
Session::set('user_id', 1);

// Mengambil nilai
$userId = Session::get('user_id');

// Menghapus nilai
Session::forget('user_id');

// Mengambil token CSRF
$csrfToken = Session::token();
```

---

## 6. Request & Response
- **`App\Core\Request`**: Direkomendasikan untuk di-type-hint di metode controller Anda untuk mengakses data permintaan (`$request->input('name')`, `$request->query('sort')`). Kini juga dapat menyimpan data pengguna yang terautentikasi (`$request->user()`).
- **`App\Core\Response`**: Metode controller **harus** mengembalikan instance dari objek ini. Gunakan metode statisnya:
  - `Response::view('home', $data)`: Mengembalikan respons HTML dari view.
  - `Response::json($data)`: Mengembalikan respons JSON.
  - `Response::redirect('/login')`: Mengembalikan respons redirect.

---

## 7. View & Templating Engine
Gunakan sintaks mirip Blade untuk menulis HTML yang bersih.

- **Menampilkan Data**: `{{ $escaped }}` (aman) dan `{!! $raw !!}` (mentah).
- **Struktur Kontrol**: `@if`, `@foreach`, `@for`, `@while`, dan penutupnya (`@endif`, dll).
- **Pewarisan Templat**: Gunakan `@extends('layout.induk')`, `@section('nama')`, dan `@yield('nama')` untuk membuat layout yang konsisten.
- **Proteksi CSRF**:
  - **Di Form**: Gunakan helper global `tuama_field()` (sebelumnya `@tuama`) di dalam `<form>` untuk membuat input token tersembunyi.
  - **Validasi**: Framework secara otomatis memvalidasi token ini untuk semua permintaan non-GET (POST, PUT, DELETE) melalui middleware global `VerifyCsrfToken`. Jika token tidak valid, error 419 (Page Expired) akan ditampilkan.

---

## 8. ORM (Object-Relational Mapping)
ORM LayVX menyediakan interaksi berorientasi objek dengan database Anda.

### a. Dasar-dasar
Query seperti `User::find(1)` sekarang mengembalikan **objek** `User`, bukan array. Anda bisa mengakses datanya sebagai properti: `$user->email`.

### b. Relasi & Konvensi
Definisikan relasi di metode `defineRelationships()` pada Model Anda.
- `SatuKeBanyak('posts', Post::class, 'user_id')`
- `BanyakKeSatu('user', User::class, 'user_id')`
- `BanyakKeBanyak('tags', Tag::class, 'post_tag')`

**Penting:** Parameter *foreign key* dan *pivot table* adalah **opsional**. Jika dihilangkan, framework akan menebaknya berdasarkan konvensi:
- **Foreign Key**: `nama_model_snake_case` + `_id` (misalnya, `user_id` untuk model `User`).
- **Pivot Table**: Gabungan nama model dalam urutan alfabet, dipisahkan oleh `_` (misalnya, `post_tag` untuk model `Post` dan `Tag`).

### c. Eager Loading
Gunakan `with()` untuk memuat relasi secara efisien: `User::with('posts', 'profile')->find($id)`.

### d. Accessors & Mutators
Manipulasi data atribut secara otomatis. Definisikan metode ini di Model Anda:
- **Accessor**: `public function getFullNameAttribute() { return $this->attributes['first_name'] . ' ' . $this->attributes['last_name']; }` (Dipanggil saat Anda mengakses `$user->full_name`).
- **Mutator**: `public function setPasswordAttribute($value) { $this->attributes['password'] = password_hash($value); }` (Dipanggil saat Anda mengatur `$user->password = 'secret'`).

---

## 9. Command Line Interface (CLI) - `layvx`
`layvx` adalah alat bantu baris perintah untuk mengotomatisasi tugas pengembangan.

- **`layvx serve`**: Menjalankan server pengembangan di `http://127.0.0.1:8000`.
- **`layvx buat:controller <Nama>`**: Membuat Controller baru.
- **`layvx buat:model <Nama> [-t]`**: Membuat Model baru. Opsi `-t` juga membuat file migrasi.
- **`layvx buat:tabel <nama>`**: Membuat file migrasi baru.
- **`layvx migrasi`**: Menjalankan migrasi database.
