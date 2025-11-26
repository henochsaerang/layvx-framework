# Tutorial Framework: Controller

Controller adalah kelas yang bertugas mengelompokkan logika-logika terkait HTTP request ke dalam satu tempat. Daripada mendefinisikan semua logika di dalam file `routes/web.php`, Anda bisa mengaturnya di dalam method-method sebuah Controller.

Controller disimpan di direktori `app/Controllers`.

## 1. Membuat Controller

Anda dapat membuat Controller secara manual atau menggunakan perintah CLI yang sudah disediakan.

### Menggunakan CLI

Untuk membuat `PostController`, jalankan perintah berikut di terminal:
```bash
layvx buat:controller PostController
```
Perintah ini akan membuat file baru di `app/Controllers/PostController.php` dengan struktur dasar sebagai berikut:

```php
<?php

namespace App\Controllers;

class PostController {
    //
}
```

## 2. Menulis Method di Controller

Method di dalam Controller dapat menerima parameter dari route dan juga dapat menerima instance `Request` untuk mendapatkan detail request yang masuk.

**Contoh:**

Misalkan kita memiliki route berikut:
```php
// routes/web.php
use App\Core\Route;

Route::get('/post/{id}', ['PostController', 'show']);
Route::post('/post', ['PostController', 'store']);
```

Maka `PostController` kita bisa terlihat seperti ini:

```php
<?php

namespace App\Controllers;

use App\Core\Request;

class PostController {

    /**
     * Menampilkan satu post berdasarkan ID.
     */
    public function show($id) {
        // Cari post dari database menggunakan Model
        $post = \App\Models\Post::find($id); // (Contoh, asumsikan model Post ada)

        // Kembalikan view 'post_detail' dengan data post
        return view('posts.detail', ['post' => $post]);
    }

    /**
     * Menyimpan post baru.
     */
    public function store(Request $request) {
        // Mengambil semua data input dari request (misal dari form)
        $data = $request->all();

        // Validasi data (logika validasi ditambahkan di sini)
        // ...

        // Buat post baru menggunakan Model
        // $newPost = \App\Models\Post::create([
        //     'title' => $data['title'],
        //     'content' => $data['content'],
        // ]);

        // Redirect ke halaman lain setelah berhasil
        return Response::redirect('/posts');
    }
}
```

### Dependency Injection

Framework ini mendukung *dependency injection* sederhana di dalam method Controller. Anda bisa melakukan *type-hint* pada kelas `App\Core\Request` untuk mendapatkan instance request saat ini, seperti yang terlihat pada method `store` di atas.

## 3. Mengembalikan Response

Sebuah method Controller harus mengembalikan sebuah response. Response dapat berupa:
-   **String:** Teks sederhana.
-   **View:** Dibuat dengan helper `view()`.
-   **JSON Response:** Dibuat dengan `Response::json([...])`.
-   **Redirect Response:** Dibuat dengan `Response::redirect('/url')`.

Contoh mengembalikan JSON:
```php
public function api_show($id) {
    $post = \App\Models\Post::find($id);
    return \App\Core\Response::json(['data' => $post]);
}
```
