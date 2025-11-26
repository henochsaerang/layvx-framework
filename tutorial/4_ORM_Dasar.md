# Tutorial Framework: ORM Dasar (Object-Relational Mapping)

ORM adalah sebuah teknik yang memungkinkan Anda berinteraksi dengan database menggunakan objek dan class PHP, alih-alih menulis query SQL secara manual. Framework ini menyediakan ORM sederhana yang kuat untuk operasi database.

Setiap tabel di database Anda memiliki sebuah "Model" yang digunakan untuk berinteraksi dengan tabel tersebut.

## 1. Membuat Model

Model disimpan di direktori `app/Models`. Anda bisa membuatnya secara manual atau menggunakan CLI.

### Menggunakan CLI
Untuk membuat model `Course`, jalankan perintah:
```bash
layvx buat:model Course
```
Ini akan menghasilkan file `app/Models/Course.php`:
```php
<?php

namespace App\Models;

use App\Core\Model;

class Course extends Model {
    //
}
```

### Konvensi dan Konfigurasi Model

-   **Nama Tabel:** Secara default, ORM akan mengasumsikan nama tabel adalah bentuk jamak dari nama kelas model dalam format *snake case*. Contoh: `Course` -> `courses`, `User` -> `users`. Jika nama tabel Anda berbeda, Anda bisa menentukannya secara manual:
    ```php
    protected static $table = 'daftar_mata_kuliah';
    ```

-   **Primary Key:** ORM mengasumsikan *primary key* setiap tabel bernama `id`. Jika berbeda, Anda bisa mengubahnya:
    ```php
    protected static $primaryKey = 'course_id';
    ```

-   **Mass Assignment (`$fillable`):** Properti `$fillable` berfungsi sebagai *whitelist* untuk kolom mana saja yang boleh diisi saat menggunakan method `create()` atau `update()` untuk mencegah masalah keamanan.
    ```php
    protected static $fillable = ['course_code', 'name', 'description', 'credits'];
    ```

## 2. Operasi Baca Data (Read)

### Mengambil Semua Data
```php
$semuaCourse = \App\Models\Course::all();

foreach ($semuaCourse as $course) {
    echo $course->name;
}
```

### Menemukan Data Berdasarkan Primary Key
```php
$course = \App\Models\Course::find(1);

echo $course->name;
```

## 3. Operasi Buat, Ubah, dan Hapus (Create, Update, Delete)

### Membuat Data Baru (Create)
Gunakan method `create()` dengan array data. Hanya atribut yang ada di `$fillable` yang akan disimpan.
```php
$courseBaru = \App\Models\Course::create([
    'course_code' => 'IF101',
    'name' => 'Dasar Pemrograman',
    'credits' => 3
]);

echo "Course baru dibuat dengan ID: " . $courseBaru->id;
```

### Mengubah Data (Update)
Gunakan `find()` untuk mencari model, ubah atributnya, atau gunakan `update()` secara langsung.
```php
// Cara 1: Find, ubah, lalu panggil method update pada instance
$course = \App\Models\Course::find(1);
// (Method update pada instance belum diimplementasikan di kode dasar,
// lebih baik gunakan update statis)

// Cara 2: Update statis berdasarkan ID
\App\Models\Course::update(1, [
    'credits' => 4
]);
```

### Menghapus Data (Delete)
```php
\App\Models\Course::delete(1);
```

## 4. Query Builder

Untuk query yang lebih kompleks, Anda bisa menggunakan Query Builder yang terintegrasi. Mulai query dengan method `query()`.

### Where Clause
```php
// Mencari course dengan SKS lebih dari 3
$courses = \App\Models\Course::query()
                ->where('credits', '>', 3)
                ->get();

// Mencari course dengan nama tertentu
$course = \App\Models\Course::query()
                ->where('course_code', '=', 'IF101')
                ->first(); // first() hanya mengambil satu hasil
```

### Order By
```php
// Mengurutkan course berdasarkan nama secara ascending (A-Z)
$courses = \App\Models\Course::query()
                ->orderBy('name', 'ASC')
                ->get();
```

### Limit & Offset
```php
// Mengambil 5 course, dimulai dari record ke-10
$courses = \App\Models\Course::query()
                ->limit(5)
                ->offset(10)
                ->get();
```
Metode-metode Query Builder bisa dirangkai (`chaining`) untuk membangun query yang Anda butuhkan sebelum akhirnya dieksekusi dengan `get()` (untuk banyak hasil) atau `first()` (untuk satu hasil).
