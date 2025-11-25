# Penjelasan Cara Kerja LayVX Framework

Dokumen ini menjelaskan arsitektur modern dan fitur-fitur utama dari LayVX Framework.

## 1. Konsep Inti

LayVX Framework dibangun di atas beberapa pilar arsitektur modern untuk mempermudah dan mempercepat pengembangan:

*   **Model-View-Controller (MVC)**: Memisahkan logika aplikasi (Model), antarmuka pengguna (View), dan alur kontrol (Controller).
*   **Object-Relational Mapper (ORM)**: Menyediakan cara yang elegan dan berorientasi objek untuk berinteraksi dengan database Anda.
*   **Service Container & Dependency Injection**: Mengelola dependensi kelas secara otomatis, membuat kode lebih modular dan mudah diuji.
*   **Component-Based System**: Fitur-fitur seperti Routing, Request, Response, dan Templating dibungkus dalam kelas-kelas komponennya sendiri.

---

## 2. Command Line Interface (CLI) - `layvx`

LayVX dilengkapi dengan alat command-line (`layvx`) untuk mengotomatisasi tugas-tugas pengembangan.

### a. Cara Kerja Internal

CLI `layvx` sekarang berarsitektur berorientasi objek:
1.  Titik masuknya adalah file `layvx/layvx`.
2.  File ini menginisialisasi **Kernel** (`App\Core\Kernel`).
3.  Kernel mendaftarkan semua kelas *command* yang tersedia dari direktori `app/Commands`.
4.  Kernel mencocokkan argumen yang Anda berikan (misalnya `buat:controller`) dengan kelas *command* yang sesuai, lalu mengeksekusi metode `handle()` dari kelas tersebut.

### b. Perintah yang Tersedia

*   **`layvx serve`**: Menjalankan server pengembangan PHP di `http://127.0.0.1:8000`.
*   **`layvx buat:controller <NamaController>`**: Membuat kelas Controller baru di `app/Controllers/`.
*   **`layvx buat:model <NamaModel> [-t]`**: Membuat kelas Model baru di `app/Models/`. Opsi `-t` juga akan membuat file migrasi yang sesuai.
*   **`layvx buat:tabel <nama_tabel>`**: Membuat file migrasi baru untuk membuat sebuah tabel.
*   **`layvx migrasi`**: Menjalankan semua migrasi database yang tertunda. Perintah ini juga akan membuat database jika belum ada.
*   **`layvx cache:clear`**: Menghapus semua file *cache view*.

---

## 3. Sistem Routing (`routes/web.php`)

Sistem *routing* bertanggung jawab untuk memetakan URL ke metode *Controller* yang sesuai. Sistem ini sekarang kuat, mendukung parameter dinamis, dan terpusat.

### a. Mendefinisikan Rute

Semua rute web didefinisikan di file `routes/web.php`. Anda harus menggunakan *facade* `App\Core\Route` untuk mendefinisikan rute.

```php
// routes/web.php
use App\Core\Route;

// Rute statis
Route::get('/', ['LandingController', 'index']);
Route::post('/login', ['AuthController', 'login']);

// Rute Dinamis
Route::get('/posts/{id}', ['PostController', 'show']);
Route::get('/users/{id}/edit', ['UserController', 'edit']);
```

### b. Rute Dinamis dan Parameter

Anda dapat mendefinisikan segmen dinamis dalam URI Anda dengan membungkusnya dalam kurung kurawal `{}`.

*   **Contoh**: `Route::get('/posts/{id}', ['PostController', 'show']);`
*   Ketika pengguna mengakses URL `/posts/123`, *router* akan mencocokkan rute ini.
*   Nilai `123` akan diekstrak dan secara otomatis **diinjeksikan** sebagai argumen ke metode *controller* yang sesuai.

### c. Injeksi Parameter dan Dependensi ke Controller

*Router* secara otomatis menyuntikkan (inject) parameter rute dan dependensi lain (seperti objek `Request`) ke dalam metode *controller* Anda berdasarkan nama dan *type-hint*.

```php
// app/Controllers/PostController.php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Post;

class PostController 
{
    // Nilai dari {id} di URL akan di-passing ke parameter $id
    public function show(Request $request, $id) 
    {
        $post = Post::find($id);

        if (!$post) {
            return Response::view('errors.404', [], 404);
        }

        return Response::view('posts.show', ['post' => $post]);
    }
}
```
**Penting:** Nama variabel di parameter metode (`$id`) harus cocok dengan nama segmen dinamis di rute (`{id}`).

---

## 4. Siklus Request & Response

Framework ini sekarang mengikuti siklus Request-Response yang modern.

*   **`App\Core\Request`**: Objek ini dibuat di awal dan membungkus semua informasi permintaan HTTP. Gunakan ini di *controller* Anda untuk mendapatkan input, header, dll.
    *   `$request->input('name')`: Mendapatkan data dari `$_POST` atau body JSON.
    *   `$request->query('sort')`: Mendapatkan data dari query string URL (`$_GET`).
    *   `$request->method()`: Mendapatkan metode HTTP (GET, POST, dll).
*   **`App\Core\Response`**: Metode *controller* Anda **harus** mengembalikan instance dari objek `Response`. Ini menyediakan cara terpusat untuk membangun respons.
    *   `Response::view('home', ['data' => $data])`: Membuat respons HTML dari sebuah *view*.
    *   `Response::json(['message' => 'Success'])`: Membuat respons JSON.
    *   `Response::redirect('/login')`: Membuat respons pengalihan (redirect).

---

## 5. View & Templating Engine

Mesin templat baru yang mirip Blade memungkinkan Anda menulis HTML yang bersih dan terstruktur.

### a. Menampilkan Data
*   `{{ $variabel }}`: Menampilkan data yang sudah di-escape (aman dari XSS).
*   `{!! $variabel !!}`: Menampilkan data mentah (hati-hati saat menggunakan).

### b. Struktur Kontrol
Gunakan direktif `@if`, `@foreach`, `@for`, `@while` seperti di Blade.
```php
@foreach($users as $user)
    <p>{{ $user->name }}</p>
@endforeach
```

### c. Pewarisan Templat
Ini adalah fitur paling kuat untuk membuat layout yang konsisten.
1.  **Buat Layout Induk** (`views/layouts/app.php`):
    ```html
    <html>
    <body>
        <header>...</header>
        <main>
            @yield('content')
        </main>
    </body>
    </html>
    ```
2.  **Extend Layout di View Anak** (`views/home.php`):
    ```php
    @extends('layouts.app')

    @section('content')
        <h1>Selamat Datang!</h1>
        <p>Ini adalah halaman utama.</p>
    @endsection
    ```
3.  **Render View Anak**: `render('home')` atau `Response::view('home')`. Mesin akan otomatis menggabungkan keduanya.

### d. CSRF Token
Gunakan direktif `@tuama` di dalam form Anda untuk menyisipkan *hidden input* CSRF token.
```html
<form method="POST">
    @tuama
    ...
</form>
```

---

## 6. ORM (Object-Relational Mapping)

ORM LayVX sekarang jauh lebih kuat, mengembalikan data sebagai objek dan mendukung fitur-fitur canggih.

### a. Hidrasi Objek
Setiap query (`all()`, `find()`, `first()`) sekarang mengembalikan **objek** dari kelas Model Anda, bukan lagi array.
```php
$user = User::find(1);
echo $user->email; // Akses sebagai properti objek
```

### b. Accessors & Mutators
Anda bisa memanipulasi atribut saat diakses atau di-set.
*   **Accessor**: `public function getNamaAttribute($value) { return ucfirst($value); }`
*   **Mutator**: `public function setPasswordAttribute($value) { $this->attributes['password'] = password_hash($value); }`

Definisikan metode ini di dalam kelas Model Anda. Mereka akan dipanggil secara otomatis.

### c. Relasi
Definisikan relasi di dalam metode `defineRelationships()` pada Model Anda.
*   `static::SatuKeBanyak('namaRelasi', ModelTerkait::class, 'foreign_key')`
*   `static::BanyakKeSatu('namaRelasi', ModelTerkait::class, 'foreign_key')`
*   `static::BanyakKeBanyak('namaRelasi', ModelTerkait::class)`

### d. Eager Loading
Gunakan metode `with()` untuk memuat relasi secara efisien dan menghindari masalah N+1 query.
```php
// Ambil semua post beserta data user dan tag-nya dalam 3 query saja.
$posts = Post::with('user', 'tags')->get();

foreach ($posts as $post) {
    echo $post->title;
    echo $post->user->name; // Akses relasi user
    foreach($post->tags as $tag) {
        echo $tag->name; // Akses relasi tags
    }
}
```

---

## 7. Migrasi Database

Gunakan `layvx buat:tabel <nama_tabel>` untuk membuat file migrasi. Edit file tersebut untuk mendefinisikan skema tabel Anda menggunakan *fluent interface* dari `col()`.

```php
// database/tabel/xxxx_create_posts_table.php
$this->createTable('posts', [
    col('id')->id(),
    col('user_id')->integer()->foreign()->references('id')->on('users'),
    col('title')->string(255)->notNullable(),
    col('body')->text(),
    timestamps(),
]);
```
Jalankan `layvx migrasi` untuk menerapkan perubahan ke database.
