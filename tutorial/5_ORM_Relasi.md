# Tutorial Framework: ORM Relasi (Relationships)

Relasi dalam ORM memungkinkan Anda untuk dengan mudah mengakses dan memanipulasi data dari tabel yang berbeda yang saling berhubungan. Misalnya, seorang `User` memiliki banyak `Post`, atau sebuah `Course` memiliki banyak `Enrollment`.

## 1. Mendefinisikan Relasi

Semua relasi untuk sebuah model didefinisikan di dalam method statis `defineRelationships()`. Framework ini menyediakan tiga jenis relasi utama.

**Contoh Struktur Model:**
```php
<?php

namespace App\Models;

use App\Core\Model;

class User extends Model {
    protected static $fillable = ['name', 'email', 'password', 'role'];

    // Panggil method untuk mendefinisikan relasi di sini
    protected static function defineRelationships() {
        // Satu User punya banyak Enrollment
        static::SatuKeBanyak('enrollments', \App\Models\Enrollment::class, 'user_id');
    }
}
```
**Penting:** Method `defineRelationships()` akan dipanggil secara otomatis oleh framework saat relasi pertama kali diakses.

---

## 2. Relasi Satu ke Banyak (SatuKeBanyak / hasMany)

Gunakan relasi ini ketika satu model memiliki banyak turunan dari model lain.
**Contoh:** Satu `Course` (mata kuliah) memiliki banyak `ClassSession` (sesi kelas).

**Definisi:**
```php
// Di dalam model App\Models\Course.php
protected static function defineRelationships() {
    // Argumen 1: Nama relasi (bebas)
    // Argumen 2: Nama kelas Model yang berhubungan
    // Argumen 3 (opsional): Foreign key di tabel 'class_sessions'
    static::SatuKeBanyak('sessions', \App\Models\ClassSession::class, 'course_id');
}
```

**Penggunaan:**
```php
// Cari sebuah course
$course = \App\Models\Course::find(1);

// Akses semua sesi kelas yang dimilikinya
$sesi_kelas = $course->sessions; // Ini akan berisi array objek ClassSession

foreach ($sesi_kelas as $sesi) {
    echo $sesi->topic;
}
```

---

## 3. Relasi Banyak ke Satu (BanyakKeSatu / belongsTo)

Gunakan relasi ini untuk mendefinisikan kebalikan dari `SatuKeBanyak`.
**Contoh:** Satu `Enrollment` (pendaftaran) dimiliki oleh satu `User` dan satu `Course`.

**Definisi:**
```php
// Di dalam model App\Models\Enrollment.php
protected static function defineRelationships() {
    // Relasi ke User
    static::BanyakKeSatu('mahasiswa', \App\Models\User::class, 'user_id');

    // Relasi ke Course
    // Argumen 3 (opsional): Foreign key di tabel 'enrollments'
    // Argumen 4 (opsional): Primary key di tabel 'courses'
    static::BanyakKeSatu('course', \App\Models\Course::class, 'course_id', 'id');
}
```

**Penggunaan:**
```php
// Cari sebuah data pendaftaran
$enrollment = \App\Models\Enrollment::find(5);

// Akses data mahasiswa yang mendaftar
$nama_mahasiswa = $enrollment->mahasiswa->name;

// Akses data mata kuliah yang diambil
$nama_matkul = $enrollment->course->name;
```

---

## 4. Relasi Banyak ke Banyak (BanyakKeBanyak / belongsToMany)

Jenis relasi ini lebih kompleks dan melibatkan tabel perantara (*pivot table*).
**Contoh:** Seorang `User` (mahasiswa) dapat mengambil banyak `Course`, dan satu `Course` dapat diambil oleh banyak `User`. Diperlukan tabel `enrollments` sebagai perantara.

**Definisi:**
```php
// Di dalam model App\Models\User.php
protected static function defineRelationships() {
    // Argumen:
    // 1. Nama relasi
    // 2. Model tujuan
    // 3. Nama tabel pivot (opsional, akan ditebak otomatis)
    // 4. Foreign key model ini di tabel pivot (opsional)
    // 5. Foreign key model tujuan di tabel pivot (opsional)
    static::BanyakKeBanyak('courses', \App\Models\Course::class, 'enrollments', 'user_id', 'course_id');
}
```

**Penggunaan:**
```php
// Cari seorang mahasiswa
$mahasiswa = \App\Models\User::find(10);

// Lihat semua mata kuliah yang dia ambil
$courses = $mahasiswa->courses;

foreach ($courses as $course) {
    echo $course->name;
}
```

---

## 5. Eager Loading (Mencegah Masalah N+1)

Saat Anda mengakses relasi seperti `$mahasiswa->courses`, ORM akan menjalankan query baru untuk mengambil data relasi tersebut. Jika Anda melakukan ini di dalam sebuah loop, Anda akan menjalankan banyak sekali query (ini disebut "N+1 Problem").

**Masalah:**
```php
$mahasiswa_list = \App\Models\User::all(); // 1 query

foreach ($mahasiswa_list as $mahasiswa) {
    // Setiap loop akan menjalankan 1 query baru untuk mengambil courses
    echo $mahasiswa->name . ' mengambil ' . count($mahasiswa->courses) . ' matkul.';
}
```
Jika ada 50 mahasiswa, kode di atas akan menjalankan 1 + 50 = 51 query!

**Solusi: Eager Loading dengan `with()`**
Gunakan method `with()` untuk memberitahu ORM agar mengambil data relasi di awal, dalam satu query tambahan.

```php
// Mengambil semua user DAN semua course yang mereka ambil hanya dalam 2 query
$mahasiswa_list = \App\Models\User::with('courses')->get(); // 'courses' adalah nama relasi

foreach ($mahasiswa_list as $mahasiswa) {
    // Tidak ada query baru yang dijalankan di sini
    echo $mahasiswa->name . ' mengambil ' . count($mahasiswa->courses) . ' matkul.';
}
```
Sekarang, kode ini hanya akan menjalankan 2 query, tidak peduli berapa pun jumlah mahasiswanya. Selalu gunakan `with()` saat Anda tahu akan mengakses relasi di dalam loop.
