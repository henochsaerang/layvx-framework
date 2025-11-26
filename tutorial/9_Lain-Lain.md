# Tutorial Framework: Fitur Lain-Lain

Dokumen ini mencakup beberapa fitur penting lainnya dari framework yang perlu Anda ketahui.

## 1. Konfigurasi Lingkungan (`.env`)

Konfigurasi yang berbeda-beda untuk setiap lingkungan (seperti lokal, staging, produksi) sebaiknya tidak ditulis langsung di dalam kode. Framework ini menggunakan file `.env` di direktori root proyek untuk mengelola konfigurasi tersebut.

### a. Cara Kerja
File `.env` berisi pasangan `KUNCI=NILAI`.
```env
# .env file
DB_HOST=localhost
DB_DATABASE=lms_unima
DB_USERNAME=root
DB_PASSWORD=

APP_ENV=development
```
Nilai-nilai ini dimuat sebagai *environment variables* saat aplikasi berjalan.

### b. Mengakses Nilai Konfigurasi
Anda dapat mengakses nilai-nilai ini melalui file-file di dalam direktori `config/`. Contohnya, `config/database.php` mengambil konfigurasi database dari `.env`:
```php
// config/database.php
return [
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'db_name' => $_ENV['DB_DATABASE'] ?? 'layvx_db',
    // ...
];
```

Untuk mengakses nilai konfigurasi ini dari mana saja di dalam aplikasi, gunakan helper `config()`:
```php
// Mengambil nama database
$dbName = config('database.db_name');

// Mengambil mode environment aplikasi dari config/app.php
$env = config('app.env'); // Akan mengembalikan 'development'
```

---

## 2. Proteksi CSRF (Cross-Site Request Forgery)

CSRF adalah serangan di mana pihak ketiga yang tidak berwenang mengirimkan perintah ke aplikasi web dari pengguna yang sudah terotentikasi. Framework ini menyediakan perlindungan CSRF secara otomatis untuk request `POST`.

### a. Cara Kerja
Middleware `\App\Middleware\VerifyCsrfToken::class` (yang terdaftar sebagai middleware global di `app/Core/Kernel.php`) akan secara otomatis memeriksa setiap request `POST`, `PUT`, `PATCH`, atau `DELETE` untuk memastikan token CSRF yang dikirim valid.

### b. Menambahkan Token ke Form
Untuk melindungi form Anda, cukup tambahkan direktif `@tuama` di dalam tag `<form>`.
```html
<form method="POST" action="/mahasiswa/profil">
    @tuama

    <input type="text" name="nama">
    <button type="submit">Simpan</button>
</form>
```
Direktif `@tuama` akan membuat sebuah input tersembunyi (`<input type="hidden" name="tuama_token" value="...">`) yang berisi token CSRF yang valid.

---

## 3. Service Container

Service Container adalah sebuah "kotak" yang mengelola dependensi kelas dan melakukan *dependency injection*. Ini adalah salah satu komponen paling kuat dalam framework, meskipun seringkali bekerja di belakang layar.

Sederhananya, jika Anda membutuhkan sebuah instance dari suatu kelas, Anda bisa memintanya dari container.

### Menggunakan Helper `app()`
Helper `app()` adalah cara termudah untuk berinteraksi dengan container.
```php
// Meminta (resolve) instance dari kelas Router
$router = app(\App\Core\Router::class);

// Di belakang layar, container akan melihat kelas Router,
// melihat bahwa ia membutuhkan instance Request di constructor-nya,
// membuat instance Request, lalu menyuntikkannya untuk membuat instance Router.
```
Anda tidak perlu khawatir tentang bagaimana cara membuat objek-objek ini, container yang akan menanganinya. Ini sangat berguna di dalam method Controller di mana *dependency injection* tidak selalu tersedia untuk semua kelas.

---

## 4. Session

Framework ini menyediakan pembungkus (wrapper) sederhana untuk mengelola session PHP. Gunakan kelas `App\Core\Session` untuk berinteraksi dengan data session.

### Menyimpan Data
```php
use App\Core\Session;

Session::set('user_id', 123);
Session::set('user', $userObject); // Bisa menyimpan objek atau array
```

### Mengambil Data
```php
$userId = Session::get('user_id');

// Anda bisa memberikan nilai default jika data tidak ditemukan
$role = Session::get('user_role', 'guest');
```

### Memeriksa Keberadaan Data
```php
if (Session::has('user_id')) {
    // Pengguna sudah login
}
```

### Menghapus Data
```php
// Menghapus satu item
Session::forget('user_id');

// Untuk logout, biasanya Anda menghancurkan seluruh session
session_destroy();
```
