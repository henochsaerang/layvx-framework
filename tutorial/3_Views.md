# Tutorial Framework: Views & Template Engine

Views berisi kode HTML aplikasi Anda dan memisahkan logika presentasi dari logika aplikasi. Semua file view disimpan di dalam direktori `views`. Framework ini dilengkapi dengan *template engine* sederhana yang terinspirasi dari Blade Laravel untuk membuat layout dan mengelola tampilan.

## 1. Membuat dan Mengembalikan View

File view adalah file `.php` biasa yang bisa berisi HTML dan PHP. Untuk mengembalikan sebuah view dari controller atau route, gunakan helper `view()`.

**Contoh:**

File view `views/salam.php`:
```php
<!DOCTYPE html>
<html>
<head>
    <title>Salam</title>
</head>
<body>
    <h1>Halo, <?php echo htmlspecialchars($nama); ?>!</h1>
</body>
</html>
```

Untuk mengembalikan view ini dari sebuah route:
```php
// routes/web.php
use App\Core\Route;

Route::get('/salam', function () {
    return view('salam', ['nama' => 'Dunia']);
});
```
-   Argumen pertama `view()` adalah nama file view menggunakan notasi titik (`.`) sebagai pemisah direktori. `view('salam')` merujuk ke `views/salam.php`. `view('auth.login')` merujuk ke `views/auth/login.php`.
-   Argumen kedua adalah array asosiatif berisi data yang ingin Anda teruskan ke view. Kunci array akan menjadi nama variabel di dalam view (misal, `['nama' => ...]` menjadi `$nama`).

## 2. Template Inheritance (Layout)

Fitur ini memungkinkan Anda mendefinisikan layout utama dan "mewariskannya" ke view lain.

### a. Mendefinisikan Layout (`@extends` & `@yield`)

Buat file layout utama, misalnya `views/layouts/app.php`:
```php
<!DOCTYPE html>
<html>
<head>
    <title>Aplikasi Saya</title>
</head>
<body>
    <header>
        <h1>Logo Aplikasi</h1>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <p>&copy; 2025 Aplikasi Saya</p>
    </footer>
</body>
</html>
```
`@yield('content')` adalah placeholder di mana konten dari view anak akan disisipkan.

### b. Mewarisi Layout (`@section`)

Sekarang, buat view anak, misalnya `views/beranda.php`, yang akan menggunakan layout ini:
```php
@extends('layouts.app')

@section('content')
    <h2>Selamat Datang di Beranda</h2>
    <p>Ini adalah konten halaman beranda.</p>
@endsection
```
-   `@extends('layouts.app')` memberitahu *template engine* bahwa view ini menggunakan layout `views/layouts/app.php`.
-   Konten di dalam `@section('content') ... @endsection` akan disisipkan ke tempat `@yield('content')` pada file layout.

## 3. Menampilkan Data

Gunakan sintaks `{{ }}` untuk menampilkan data. Data ini akan otomatis di-escape (dibersihkan dari tag HTML berbahaya) untuk mencegah serangan XSS.

```php
// Menampilkan variabel $user->name
Halo, {{ $user->name }}.

// Ini setara dengan:
Halo, <?php echo htmlspecialchars($user->name); ?>.
```

Jika Anda **yakin** bahwa data Anda aman dan perlu menampilkan HTML mentah, gunakan sintaks `{!! !!}`.

```php
// Variabel $post->content berisi '<h3>Judul</h3><p>Isi...</p>'
{!! $post->content !!}
```
**Peringatan:** Gunakan `{!! !!}` dengan sangat hati-hati dan hanya pada data yang Anda percayai.

## 4. Control Structures

*Template engine* menyediakan shortcut untuk struktur kontrol PHP.

-   `@if`, `@elseif`, `@else`, `@endif`
    ```php
    @if (count($records) === 1)
        Saya punya satu record!
    @elseif (count($records) > 1)
        Saya punya banyak record!
    @else
        Saya tidak punya record.
    @endif
    ```

-   `@foreach`, `@endforeach`
    ```php
    @foreach ($users as $user)
        <p>Nama: {{ $user->name }}</p>
    @endforeach
    ```

## 5. CSRF Field (`@tuama`)

Untuk melindungi form dari serangan *Cross-Site Request Forgery*, gunakan direktif `@tuama` di dalam form Anda. Ini akan menghasilkan input field tersembunyi yang berisi token CSRF.

```html
<form method="POST" action="/profil">
    @tuama

    <!-- Input lainnya -->
</form>
```
Ini setara dengan memanggil fungsi `tuama_field()`.
