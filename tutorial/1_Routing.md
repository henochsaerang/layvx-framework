# Tutorial Framework: Routing

Routing bertanggung jawab untuk memetakan URL yang diminta oleh pengguna ke logika aplikasi yang sesuai, baik itu sebuah fungsi sederhana atau sebuah method di dalam Controller.

## 1. Route Dasar

Route paling dasar menerima sebuah URI dan sebuah Closure (fungsi anonim). Semua definisi route berada di file `routes/web.php`.

**Contoh:**

```php
// routes/web.php

use App\Core\Route;

// Route untuk halaman utama
Route::get('/', function () {
    return 'Selamat Datang!';
});

// Route untuk halaman /tentang
Route::get('/tentang', function () {
    return view('tentang_kami'); // Merender file views/tentang_kami.php
});

// Route yang menangani request POST
Route::post('/kontak', function () {
    // Logika untuk memproses form kontak
    // ...
    return 'Terima kasih telah menghubungi kami.';
});
```

## 2. Route dengan Parameter

Terkadang Anda perlu menangkap segmen dari URI, misalnya ID pengguna. Anda dapat melakukannya dengan mendefinisikan parameter route.

**Contoh:**

```php
// routes/web.php

use App\Core\Route;

// Menangkap ID pengguna dari URL
Route::get('/user/{id}', function ($id) {
    return 'ID Pengguna: ' . $id;
});

// Menangkap slug untuk sebuah artikel blog
Route::get('/post/{slug}', function ($slug) {
    // Logika untuk mencari artikel berdasarkan slug
    // ...
    return "Menampilkan artikel: {$slug}";
});
```
Parameter dibungkus dengan kurung kurawal `{}` dan nama variabelnya harus terdiri dari huruf.

## 3. Route ke Controller

Untuk logika yang lebih kompleks, lebih baik mengelompokkannya di dalam sebuah `Controller`. Anda dapat mengarahkan route ke sebuah method Controller.

**Contoh:**

Misalkan Anda memiliki `app/Controllers/UserController.php`:
```php
<?php

namespace App\Controllers;

class UserController {
    public function show($id) {
        // Logika untuk mengambil data user dari database
        $user = \App\Models\User::find($id);

        if (!$user) {
            return view('errors.404');
        }

        return view('profil_user', ['user' => $user]);
    }
}
```

Maka route-nya akan didefinisikan seperti ini:
```php
// routes/web.php

use App\Core\Route;

// Arahkan URL /user/{id} ke method 'show' di dalam 'UserController'
Route::get('/user/{id}', ['UserController', 'show']);
```
Framework akan secara otomatis membuat instance `UserController` dan memanggil method `show`, sambil menyuntikkan parameter `$id` dari URL.
