# Tutorial Framework: Migrasi Database

Migrasi (migrations) adalah sistem kontrol versi (seperti Git) untuk skema database Anda. Migrasi memungkinkan Anda untuk mendefinisikan dan membagikan skema tabel database aplikasi secara terstruktur dalam bentuk file PHP.

## 1. Manfaat Migrasi
-   **Kolaborasi Tim:** Setiap anggota tim dapat menjalankan file migrasi untuk mendapatkan struktur database yang sama persis di lingkungan lokal mereka.
-   **Pelacakan Perubahan:** Setiap perubahan pada struktur database (tambah tabel, tambah kolom, dll.) dilacak dalam sebuah file migrasi baru.
-   **Deployment Mudah:** Saat melakukan deployment, Anda hanya perlu menjalankan perintah migrasi untuk membangun skema database di server produksi.

## 2. Membuat Migrasi

Gunakan perintah CLI `layvx` untuk membuat file migrasi baru. File-file ini akan ditempatkan di `database/tabel`.

### a. Membuat Tabel Baru
Gunakan perintah `buat:tabel`. Penamaan file sangat penting. Ikuti format `create_[nama_tabel]_table`.
```bash
# Membuat file migrasi untuk tabel 'courses'
layvx buat:tabel create_courses_table
```
Perintah ini akan membuat file dengan nama seperti `2025_11_26_123456_create_courses_table.php`. Framework akan secara otomatis mencoba menebak nama kelas dari nama file tersebut (misal: `CreateCoursesTable`).

### b. Mengubah Tabel yang Sudah Ada
Meskipun framework ini tidak secara eksplisit menyediakan perintah untuk mengubah tabel, Anda bisa membuat file migrasi dengan nama yang deskriptif.
```bash
# Contoh: migrasi untuk menambahkan kolom 'description' ke tabel 'courses'
layvx buat:tabel add_description_to_courses_table
```

## 3. Struktur File Migrasi

Setiap file migrasi berisi sebuah kelas dengan dua method utama: `up()` dan `down()`.

-   `up()`: Method ini dieksekusi saat Anda menjalankan migrasi. Tugasnya adalah **menerapkan** perubahan ke database (misalnya, membuat tabel atau menambahkan kolom).
-   `down()`: Method ini (saat ini belum ada perintah untuk menjalankannya, namun best practice untuk diisi) bertugas untuk **mengembalikan** perubahan yang dibuat oleh method `up()` (misalnya, menghapus tabel).

**Contoh file migrasi `..._create_courses_table.php`:**
```php
<?php

use App\Core\Migration;
use App\Core\Column;

class CreateCoursesTable extends Migration
{
    public function up()
    {
        $this->createTable('courses', [
            col()->id(), // Kolom 'id' auto-increment primary key
            col('course_code')->string(10)->unique(),
            col('name')->string(),
            col('description')->text()->nullable(),
            col('credits')->integer(),
            timestamps() // Membuat kolom 'created_at' dan 'updated_at'
        ]);
    }

    public function down()
    {
        $this->dropTable('courses');
    }
}
```

## 4. Schema Builder

Di dalam method `up()`, Anda dapat mendefinisikan kolom-kolom tabel menggunakan *schema builder* yang disediakan.

-   `col('nama_kolom')`: Memulai definisi sebuah kolom.
-   `timestamps()`: Helper untuk membuat kolom `created_at` dan `updated_at` secara otomatis.

### Tipe Kolom
-   `id()` atau `increments()`: `BIGINT` Auto-Increment, Primary Key.
-   `string(panjang)`: `VARCHAR` (default panjang 255).
-   `text()`: `TEXT`.
-   `integer()`: `INT`.
-   `timestamp()`: `TIMESTAMP`.
-   `enum(['nilai1', 'nilai2'])`: `ENUM`.

### Modifier Kolom
Modifier ini bisa dirangkai (`chaining`) setelah tipe kolom.
-   `nullable()`: Mengizinkan kolom memiliki nilai `NULL`.
-   `unique()`: Menambahkan *unique constraint* pada kolom.
-   `default('nilai')`: Menentukan nilai default untuk kolom.
-   `primary()`: Menjadikan kolom sebagai *primary key*.

### Foreign Key
Untuk mendefinisikan *foreign key*:
```php
class CreateEnrollmentsTable extends Migration
{
    public function up()
    {
        $this->createTable('enrollments', [
            col()->id(),
            col('user_id')->integer()->foreign()->references('id')->on('users')->onDelete('CASCADE'),
            col('course_id')->integer()->foreign()->references('id')->on('courses')->onDelete('CASCADE'),
            timestamps()
        ]);
    }
    // ...
}
```
-   `foreign()`: Menandai kolom ini sebagai *foreign key*.
-   `references('id')`: Merujuk ke kolom `id`.
-   `on('users')`: Di tabel `users`.
-   `onDelete('CASCADE')`: Jika data di tabel parent dihapus, data di tabel ini juga ikut terhapus. Opsi lain: `SET NULL`, `RESTRICT`.

## 5. Menjalankan Migrasi

Untuk menjalankan semua migrasi yang belum pernah dijalankan, gunakan perintah `migrasi`:
```bash
layvx migrasi
```
Framework akan memeriksa tabel `migrations` di database untuk melihat file mana yang sudah dijalankan dan hanya akan menjalankan yang baru.
