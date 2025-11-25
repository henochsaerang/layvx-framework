# LayVX Framework

![LayVX Logo Placeholder](https://via.placeholder.com/150x50?text=LayVX+Framework)

LayVX adalah sebuah PHP framework MVC (Model-View-Controller) ringan yang dirancang untuk pengembangan aplikasi web yang cepat dan terstruktur. Dengan fitur-fitur modern seperti Dependency Injection, Middleware Pipeline, dan ORM, LayVX menyediakan fondasi yang kokoh untuk membangun aplikasi web yang scalable dan mudah dikelola.

## Fitur Utama

-   **Arsitektur MVC**: Pemisahan yang jelas antara logika bisnis, presentasi, dan data.
-   **Service Container & Dependency Injection**: Manajemen dependensi yang kuat untuk kode yang lebih modular dan mudah diuji.
-   **Middleware Pipeline**: Sistem middleware yang fleksibel untuk memproses request HTTP sebelum dan sesudah mencapai controller, termasuk middleware global dan route-specific dengan grouping.
-   **Manajemen Sesi Abstrak**: Class `App\Core\Session` untuk pengelolaan sesi yang aman dan fleksibel.
-   **ORM (Object-Relational Mapping)**: Interaksi database berbasis objek dengan pemetaan Model-ke-Tabel otomatis atau manual, dan dukungan relasi dasar.
-   **Templating Engine**: Sintaks templating mirip Blade untuk tampilan yang bersih dan dinamis.
-   **Error Handling Robust**: Penanganan error yang informatif di lingkungan development dan generik di produksi.
-   **Command Line Interface (CLI)**: Alat bantu `layvx` untuk mengotomatisasi tugas-tugas umum seperti membuat controller, model, dan menjalankan migrasi.

## Instalasi

Ikuti langkah-langkah berikut untuk menginstal dan menjalankan LayVX Framework di lingkungan lokal Anda:

1.  **Clone Repositori**:
    ```bash
    git clone [URL_REPO_ANDA] namaproject
    cd namaproject
    ```
    *(Ganti `[URL_REPO_ANDA]` dengan URL repositori Git Anda)*

2.  **Konfigurasi Environment**:
    *   Buat file `.env` di root proyek Anda dengan menyalin `example.env` (jika ada) atau membuatnya secara manual.
    *   Isi detail koneksi database dan pengaturan aplikasi lainnya.
    ```env
    APP_ENV=development
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=layvx_db
    DB_USERNAME=root
    DB_PASSWORD=
    ```

3.  **Setup Database**:
    *   Buat database dengan nama yang sama dengan `DB_DATABASE` di `.env` Anda.
    *   Jalankan migrasi database untuk membuat tabel:
        ```bash
        php layvx migrasi
        ```

4.  **Konfigurasi Web Server (Apache/Nginx)**:
    *   Arahkan *document root* server web Anda ke direktori `/public` di dalam proyek Anda. Ini penting untuk keamanan.
    *   **Contoh untuk Apache (.htaccess di root /public):** (Pastikan `mod_rewrite` diaktifkan)
        ```apache
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteBase /

            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ index.php [QSA,L]
        </IfModule>
        ```

## Penggunaan Dasar

### Menjalankan Server Pengembangan
```bash
php layvx serve
```
Aplikasi Anda akan dapat diakses di `http://127.0.0.1:8000`.

### Routing
Definisikan rute aplikasi Anda di `routes/web.php`.

```php
// routes/web.php
use App\Core\Route;
use App\Core\Response;
use App\Core\Session;

Route::get('/', ['LandingController', 'index']);

// Grup rute yang dilindungi middleware otentikasi admin
Route::group(['middleware' => 'auth.admin'], function ($router) {
    $router->get('/admin/dashboard', ['DashboardController', 'index']);
});

// Contoh rute API
Route::get('/api/data', function() {
    return Response::json(['message' => 'Data from API', 'status' => 'success']);
});
```

### Model (ORM)
Berinteraksi dengan database menggunakan Model.

```php
// app/Models/Admin.php
namespace App\Models;
use App\Core\Model;

class Admin extends Model
{
    protected static $table = 'admins';
    protected static $primaryKey = 'id_admin';
    protected static $fillable = ['nama', 'email', 'password'];
}
```

```php
// Contoh penggunaan di Controller
use App\Models\Admin;

class DashboardController {
    public function index() {
        $admins = Admin::all(); // Mengambil semua admin
        $admin = Admin::find(1); // Mencari admin dengan ID 1
        // ...
    }
}
```

### Command Line Interface (CLI)
Gunakan `php layvx` untuk melihat daftar perintah yang tersedia.

```bash
php layvx buat:controller NamaController
php layvx buat:model NamaModel -t
php layvx migrasi
```

## Kontribusi

Kami menyambut kontribusi Anda! Jika Anda menemukan bug atau memiliki saran fitur, silakan buka *issue* atau kirimkan *pull request*.

## Lisensi

(Sertakan informasi lisensi di sini, contoh: MIT License)
