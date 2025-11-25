# Penjelasan Cara Kerja LayVX Framework

Dokumen ini menjelaskan arsitektur Model-View-Controller (MVC) dan implementasi Simple ORM (Object-Relational Mapping) pada LayVX Framework.

## 1. Arsitektur Model-View-Controller (MVC)

LayVX Framework mengikuti pola arsitektur MVC, yang memisahkan aplikasi menjadi tiga komponen utama:

### a. Model (`app/Core/Model.php`, `app/Models/*.php`, `app/Core/QueryBuilder.php`)

*   **Tanggung Jawab**: Model bertanggung jawab atas data aplikasi, logika bisnis, dan interaksi dengan database.
*   **Implementasi di LayVX**:
    *   `app/Core/Model.php`: Ini adalah kelas dasar (base class) untuk semua model Anda. Ia menyediakan fungsionalitas inti untuk koneksi database, metode CRUD (Create, Read, Update, Delete) dasar, dan integrasi dengan `QueryBuilder`. Model ini juga menangani definisi relasi antar tabel (SatuKeBanyak, BanyakKeSatu) dan transaksi database.
    *   `app/Models/*.php`: Setiap file di sini adalah model spesifik untuk sebuah tabel database (misalnya `User.php`, `Course.php`). Kelas-kelas ini meng-extend `Model.php` dan mendefinisikan properti statis `$table` yang menunjuk ke nama tabel database yang sesuai. Mereka juga dapat mendefinisikan relasi menggunakan metode `defineRelationships()`.
    *   `app/Core/QueryBuilder.php`: Kelas ini adalah inti dari interaksi database. Ia menyediakan antarmuka yang fasih (fluent interface) untuk membangun query SQL secara dinamis (SELECT, INSERT, UPDATE, DELETE, WHERE, JOIN, dll). `Model.php` menggunakan `QueryBuilder` untuk melakukan operasi databasenya.

### b. View (`views/**/*.php`, `app/Core/helpers.php`)

*   **Tanggung Jawab**: View bertanggung jawab atas presentasi data kepada pengguna. Mereka berisi kode HTML, CSS, dan JavaScript, dengan sedikit logika PHP untuk menampilkan data yang disediakan oleh Controller.
*   **Implementasi di LayVX**:
    *   `views/**/*.php`: File-file ini adalah template tampilan Anda (misalnya `views/auth/login.php`, `views/dosen/attendance_index.php`). Mereka berisi struktur HTML dan PHP yang disematkan untuk menampilkan data.
    *   `app/Core/helpers.php` (fungsi `render()`): Fungsi `render` adalah mekanisme utama untuk memuat dan menampilkan View. Ia mengambil nama view dan array data, kemudian meng-ekstrak data tersebut agar tersedia sebagai variabel di dalam scope view, lalu menyertakan (require) file view tersebut. Sistem caching view dasar juga ada di sini, yang mengkompilasi view ke `storage/framework/views` untuk kinerja.

### c. Controller (`app/Controllers/*.php`, `routes/web.php`, `public/index.php`)

*   **Tanggung Jawab**: Controller bertindak sebagai jembatan antara Model dan View. Ia menerima permintaan dari pengguna, memprosesnya (mungkin dengan berinteraksi dengan Model), dan kemudian meneruskan data yang relevan ke View untuk ditampilkan.
*   **Implementasi di LayVX**:
    *   `public/index.php`: Ini adalah *front controller* aplikasi. Semua permintaan masuk melalui file ini. Ia bertanggung jawab untuk:
        *   Memuat konfigurasi dan helper awal.
        *   Mendapatkan URL yang diminta.
        *   Memuat definisi rute dari `routes/web.php`.
        *   Mencocokkan URL dengan rute yang terdaftar.
        *   Memanggil Controller dan metode yang sesuai.
    *   `routes/web.php`: File ini mendefinisikan semua rute aplikasi. Setiap rute memetakan URL ke pasangan `Controller@method` yang akan menangani permintaan.
    *   `app/Controllers/*.php`: Setiap file di sini adalah Controller spesifik (misalnya `AuthController.php`, `CourseController.php`). Kelas-kelas ini berisi metode yang langsung dipanggil oleh *router* (melalui `public/index.php`). Metode-metode ini bertanggung jawab untuk memproses input pengguna, berinteraksi dengan Model (misalnya mengambil data dari database), dan kemudian memanggil fungsi `render()` untuk menampilkan View.

## 2. Simple ORM (Object-Relational Mapping)

LayVX Framework menyediakan ORM sederhana untuk mempermudah interaksi dengan database tanpa harus menulis query SQL secara manual. Ini dicapai melalui kombinasi `Model.php` dan `QueryBuilder.php`.

### Penting: Hasil Query Adalah Array Asosiatif

Perlu diperhatikan bahwa metode-metode query seperti `get()`, `first()`, `find()`, `where()` dari ORM LayVX akan mengembalikan data sebagai **array asosiatif PHP standar**, bukan objek. Ini berarti Anda harus mengakses nilai kolom menggunakan sintaks array (misalnya `$user['nama']` atau `$user['id']`), dan **BUKAN** notasi objek (yaitu `$user->nama` atau `$user->id`).

### a. `app/Core/Model.php`

*   **Dasar ORM**: Kelas `Model` adalah dasar dari ORM. Ia mengabstraksi detail koneksi database (`self::connect()`) dan menyediakan metode statis yang mudah digunakan untuk berinteraksi dengan tabel database.
*   **Mass Assignment Protection dengan `$fillable`**:
    *   Properti `protected static $fillable = [];` sekarang **aktif** di `Model.php` untuk menyediakan mekanisme *mass assignment protection*.
    *   Anda harus mendefinisikan array string dari nama-nama kolom database yang **boleh** diisi (assign) melalui metode `create()`, `update()`, atau `updateWhere()` di setiap model anak Anda.
    *   Ini adalah fitur keamanan penting untuk mencegah kolom sensitif (seperti `id`, `password`, `is_admin`) diubah secara tidak sengaja atau berbahaya melalui input pengguna. Hanya kolom yang terdaftar di `$fillable` yang akan diterima saat melakukan operasi massal.
    *   **Contoh Penggunaan di Model Anak**:
        ```php
        // app/Models/User.php
        class User extends Model {
            protected static $table = 'users';
            protected static $fillable = ['nama', 'email', 'password', 'profile_picture']; // Kolom yang boleh diisi
            // ...
        }
        ```
*   **Metode CRUD Dasar**:
    *   `static::all()`: Mengambil semua record.
    *   `static::find($id)`: Menemukan record berdasarkan primary key.
    *   `static::where($column, $value)`: Menemukan record berdasarkan kondisi WHERE.
    *   `static::create(array $data)`: Membuat record baru.
    *   `static::update($id, array $data)`: Memperbarui record berdasarkan primary key.
    *   `static::delete($id)`: Menghapus record berdasarkan primary key.
*   **Relasi**: Mendukung relasi `SatuKeBanyak` (hasMany) dan `BanyakKeSatu` (belongsTo) yang dapat didefinisikan dalam model turunan. Ini memungkinkan pemuatan data terkait (eager loading) menggunakan `static::with('relation_name')`.
*   **Transaksi Database**: Metode `static::transaction(callable $callback)` memungkinkan Anda menjalankan serangkaian operasi database dalam sebuah transaksi, memastikan atomisitas (semua berhasil atau semua gagal).

### b. `app/Core/QueryBuilder.php`

*   **Membangun Query Dinamis**: Ini adalah kelas yang membangun query SQL sebenarnya. Kelas `Model` menggunakan `QueryBuilder` untuk menghasilkan pernyataan SQL yang diperlukan berdasarkan metode-metode yang dipanggil (misalnya `select()`, `where()`, `join()`, `orderBy()`, `get()`, `insertOrUpdate()`, `update()`, `delete()`).
*   **Antarmuka Fasih (Fluent Interface)**: `QueryBuilder` memungkinkan Anda merangkai panggilan metode (method chaining) untuk membangun query yang kompleks dengan cara yang mudah dibaca dan intuitif.
*   **Koneksi Database**: `QueryBuilder` menerima instance PDO dari `Model::connect()` untuk mengeksekusi query.

### Contoh Alur Kerja ORM (dengan akses array dan penggunaan $fillable):

```php
// Mengambil semua user
$users = User::all(); // Mengembalikan array asosiatif dari user

// Mencari user berdasarkan ID
$user = User::find(1); // Mengembalikan array asosiatif dari user, atau null

if ($user) {
    echo "Nama User: " . $user['nama'] . "\n"; // Akses menggunakan sintaks array
    echo "Email User: " . $user['email'] . "\n";
}

// Mencari user dengan kondisi tertentu
$activeUsers = User::where('is_active', 1)->get(); // Mengembalikan array dari array asosiatif

// Membuat user baru (hanya kolom di $fillable User yang akan dipertimbangkan)
$newUser = User::create([
    'nama' => 'Budi', 
    'email' => 'budi@example.com', 
    'password' => password_hash('password123', PASSWORD_DEFAULT)
]);

// Memperbarui user (hanya kolom di $fillable User yang akan dipertimbangkan)
User::update(1, [
    'nama' => 'Budi Santoso', 
    'profile_picture' => 'new_pic.jpg'
]);

// Menghapus user
User::delete(1);

// Mengambil user beserta kursus yang diambil (jika relasi didefinisikan)
$userWithCourses = User::with('courses')->find(1);
if ($userWithCourses) {
    echo "Nama Dosen: " . $userWithCourses['nama'] . "\n";
    // Akses relasi juga akan mengembalikan array
    foreach ($userWithCourses['courses'] as $course) {
        echo "- Kursus: " . $course['nama_course'] . "\n";
    }
}


// Menjalankan operasi dalam transaksi
Model::transaction(function($db) {
    // Lakukan operasi database di sini
    // Misalnya: User::create(...); Course::update(...);
    // Jika ada error, transaksi akan di-rollback
});
```

Dengan kombinasi MVC dan Simple ORM ini, LayVX Framework dirancang untuk memudahkan pengembangan aplikasi web PHP dengan memisahkan kekhawatiran dan menyediakan alat yang efisien untuk berinteraksi dengan database.

## 2.1 Sintaks Tabel (Migrasi)

Migrasi adalah cara untuk mengelola skema database Anda menggunakan kode PHP. Setiap file migrasi mendefinisikan perubahan struktur database Anda (misalnya membuat tabel, menambah kolom, mengubah tipe data).

### a. Struktur File Migrasi

Setiap migrasi adalah kelas PHP yang meng-extend kelas `Migration` (didefinisikan di `app/Core/Migration.php`). Kelas ini memiliki dua metode utama:

*   **`up()`**: Metode ini berisi logika untuk menerapkan perubahan ke database (misalnya membuat tabel baru).
*   **`down()`**: Metode ini berisi logika untuk mengembalikan perubahan yang dilakukan di metode `up()` (misalnya menghapus tabel yang dibuat).

**Contoh Struktur Migrasi:**

```php
<?php

require_once __DIR__ . '/../../app/Core/helpers.php'; // Untuk fungsi col() dan timestamps()

class CreateUsersTable extends Migration {
    public function up() {
        // Logika untuk membuat atau memodifikasi tabel
    }

    public function down() {
        // Logika untuk mengembalikan perubahan
    }
}
```

### b. Membuat dan Memodifikasi Tabel

Di dalam metode `up()`, Anda akan menggunakan metode `$this->createTable()` atau `$this->table()` (untuk memodifikasi tabel yang sudah ada) yang disediakan oleh kelas `Migration`.

#### `createTable(string $tableName, array $columns)`

Metode ini digunakan untuk membuat tabel baru.

*   `$tableName`: Nama tabel yang akan dibuat.
*   `$columns`: Sebuah array yang berisi definisi kolom-kolom tabel. Setiap definisi kolom dibuat menggunakan fungsi helper global `col()` dan method chaining.

**Contoh `createTable` (dari `2025_11_20_072942_create_users_table.php`):**

```php
$this->createTable('users', [
    col('id')->id(),                                 // Integer, Primary Key, Auto-Increment
    col('nama')->string(100)->notNullable(),         // VARCHAR(100), Tidak boleh NULL
    col('email')->string(100)->unique()->notNullable(), // VARCHAR(100), Unik, Tidak boleh NULL
    col('password')->string(255)->notNullable(),     // VARCHAR(255), Tidak boleh NULL
    col('role')->enum(['Student','Dosen','Admin'])->notNullable(), // ENUM, Tidak boleh NULL
    col('profile_picture')->string(255)->nullable(), // VARCHAR(255), Boleh NULL
    col('password_reset_token')->string(255)->nullable(), // VARCHAR(255), Boleh NULL
    col('password_reset_expires')->timestamp()->nullable(), // TIMESTAMP, Boleh NULL
    timestamps(),                                    // Otomatis menambahkan created_at dan updated_at
]);
```

#### Fungsi Helper `col()` dan Class `Column`

Fungsi global `col(string $name)` (didefinisikan di `app/Core/helpers.php`) mengembalikan instance dari kelas `Column`. Kelas `Column` menyediakan *fluent interface* (method chaining) untuk mendefinisikan properti kolom.

**Tipe Kolom:**

*   `id()` / `increments()`: Membuat kolom `INT PRIMARY KEY AUTO_INCREMENT`.
*   `string(length)`: Membuat kolom `VARCHAR` dengan panjang yang ditentukan (default 255).
*   `integer()`: Membuat kolom `INT`.
*   `text()`: Membuat kolom `TEXT`.
*   `enum(array $values)`: Membuat kolom `ENUM` dengan nilai-nilai yang diizinkan.
*   `timestamp()`: Membuat kolom `TIMESTAMP`.

**Modifikasi Kolom:**

*   `nullable()`: Mengizinkan kolom memiliki nilai `NULL`.
*   `notNullable()`: Kolom tidak boleh `NULL` (default jika tidak ditentukan dan bukan primary key).
*   `unique()`: Menambahkan constraint `UNIQUE` pada kolom.
*   `default($value)`: Menetapkan nilai default untuk kolom.
*   `onUpdate(string $value)`: Digunakan untuk kolom `TIMESTAMP` untuk mengatur aksi `ON UPDATE` (misalnya `CURRENT_TIMESTAMP`).

**Foreign Keys:**

Anda dapat mendefinisikan *foreign key* menggunakan method chaining pada `col()`:

```php
col('user_id')->integer()->foreign()->references('id')->on('users')->onDelete('CASCADE')->onUpdateFK('CASCADE'),
```

*   `foreign()`: Menandai kolom sebagai foreign key.
*   `references(string $column)`: Menentukan kolom primary key yang diacu di tabel asing.
*   `on(string $table)`: Menentukan tabel asing yang diacu.
*   `onDelete(string $action)`: Menentukan aksi saat record di tabel asing dihapus (`CASCADE`, `SET NULL`, `RESTRICT`, `NO ACTION`).
*   `onUpdateFK(string $action)`: Menentukan aksi saat record di tabel asing diperbarui (`CASCADE`, `SET NULL`, `RESTRICT`, `NO ACTION`).

#### Fungsi Helper `timestamps()`

Fungsi global `timestamps()` (didefinisikan di `app/Core/helpers.php`) adalah shortcut yang menambahkan dua kolom ke tabel Anda:
*   `created_at`: `TIMESTAMP` dengan `DEFAULT CURRENT_TIMESTAMP`.
*   `updated_at`: `TIMESTAMP` dengan `DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`.

### c. Menghapus Tabel

Di dalam metode `down()`, Anda dapat menggunakan metode `$this->dropTable(string $tableName)` untuk menghapus tabel.

```php
public function down() {
    $this->dropTable('users');
}
```

## 2.2 Sintaks Model

Setiap Model di LayVX Framework adalah representasi dari sebuah tabel di database Anda. Model berfungsi sebagai antarmuka untuk berinteraksi dengan data tabel tersebut menggunakan ORM.

### a. Struktur Dasar Model

Setiap kelas model harus meng-extend kelas `Model` (yang sekarang berada di `app/Core/Model.php`).

**Contoh Struktur Dasar (dari `app/Models/User.php`):**

```php
<?php

require_once '../Core/Model.php'; // Path baru ke kelas Model dasar

class User extends Model {
    protected static $table = 'users'; // Nama tabel yang terkait dengan model ini

    // Metode kustom atau definisi relasi lainnya akan ditempatkan di sini
}
```

*   **`protected static $table = 'nama_tabel';`**: Properti statis ini sangat penting. Ia memberi tahu ORM tabel database mana yang terkait dengan model ini. Pastikan nama tabel sesuai dengan tabel di database Anda.

### b. Mendefinisikan Relasi

Model LayVX mendukung definisi relasi antar tabel menggunakan metode `defineRelationships()`. Metode ini menggunakan helper statis dari `Model.php`.

```php
// app/Models/User.php (Contoh dengan relasi)

require_once '../Core/Model.php';
require_once 'Course.php'; // Include model terkait
require_once 'Enrollment.php';

class User extends Model {
    protected static $table = 'users';

    // Definisi relasi menggunakan helper statis
    protected static function defineRelationships() {
        // User (Dosen) memiliki banyak Course
        static::SatuKeBanyak('courses', Course::class, 'dosen_id'); 
        
        // User (Mahasiswa) memiliki banyak Enrollment
        static::SatuKeBanyak('enrollments', Enrollment::class, 'user_id'); 
    }

    // Metode kustom lainnya
    public static function findByEmail($email) {
        return static::query()->whereEquals('email', $email)->first();
    }
}
```

*   **`protected static function defineRelationships()`**: Metode ini adalah tempat Anda mendeklarasikan semua relasi yang dimiliki model.
*   **`static::SatuKeBanyak('nama_relasi', KelasTerkait::class, 'foreign_key')`**: Mendefinisikan relasi "satu ke banyak" (hasMany).
    *   `nama_relasi`: Nama yang akan Anda gunakan untuk mengakses relasi ini (misalnya `$user->courses`).
    *   `KelasTerkait::class`: Nama kelas model yang terkait (misalnya `Course::class`).
    *   `foreign_key`: Kolom di tabel *terkait* yang mengacu kembali ke primary key model saat ini (misalnya `dosen_id` di tabel `courses` mengacu ke `id` di tabel `users`).
*   **`static::BanyakKeSatu('nama_relasi', KelasTerkait::class, 'foreign_key')`**: Mendefinisikan relasi "banyak ke satu" (belongsTo).
    *   `nama_relasi`: Nama yang akan Anda gunakan untuk mengakses relasi ini (misalnya `$course->dosen`).
    *   `KelasTerkait::class`: Nama kelas model yang terkait (misalnya `User::class`).
    *   `foreign_key`: Kolom di tabel *model saat ini* yang mengacu ke primary key model *terkait* (misalnya `dosen_id` di tabel `courses` mengacu ke `id` di tabel `users`).

### c. Metode Kustom Model

Anda dapat menambahkan metode statis atau non-statis kustom ke model Anda untuk mengabstraksi logika bisnis spesifik. Metode-metode ini seringkali akan menggunakan metode ORM dasar (`find()`, `where()`, `create()`, `update()`, `delete()`, `query()`) yang disediakan oleh kelas `Model` dasar untuk berinteraksi dengan database.

**Contoh Metode Kustom (dari `app/Models/User.php`):**

```php
public static function findByEmail($email) {
    return static::query()->whereEquals('email', $email)->first();
}

public static function updateDetails($userId, $nama, $email, $pictureFilename) {
    return static::update($userId, [
        'nama' => $nama,
        'email' => $email,
        'profile_picture' => $pictureFilename
    ]);
}
```

Dengan memahami sintaks migrasi dan model ini, Anda dapat secara efektif mendefinisikan struktur database dan berinteraksi dengan data aplikasi Anda menggunakan LayVX Framework.

## 3. Command Line Interface (CLI)

LayVX Framework dilengkapi dengan alat command-line (`layvx`) yang membantu Anda dalam berbagai tugas pengembangan. Anda dapat menjalankan perintah ini dari terminal Anda dengan menavigasi ke direktori root proyek (`C:\laragon\www\layvx`) dan menggunakan sintaks `.\layvx <command>` (di PowerShell) atau `layvx <command>` (di Command Prompt).

Berikut adalah daftar perintah yang tersedia:

### `.\layvx serve`

*   **Deskripsi**: Menjalankan server pengembangan PHP internal. Ini sangat berguna untuk pengembangan lokal karena menyediakan server web sederhana tanpa perlu konfigurasi Apache atau Nginx secara manual.
*   **Contoh**: `.\layvx serve`
*   **Output**: Server akan berjalan di `http://127.0.0.1:8000` (atau port lain jika Anda mengaturnya).

### `.\layvx buat:tabel <nama_tabel>`

*   **Deskripsi**: Membuat file migrasi baru untuk membuat tabel database. Migrasi adalah "kontrol versi" untuk database Anda, memungkinkan Anda mendefinisikan skema database dalam kode.
*   **Parameter**:
    *   `<nama_tabel>`: Nama tabel yang ingin Anda buat (misalnya `users`, `products`, `orders`).
*   **Contoh**: `.\layvx buat:tabel users`
*   **Output**: Akan membuat file di `database/tabel/` dengan format `YYYY_MM_DD_HHMMSS_create_nama_tabel_table.php`. Anda perlu mengisi detail kolom tabel secara manual di file tersebut.

### `.\layvx buat:hapus_tabel <nama_tabel>`

*   **Deskripsi**: Membuat file migrasi baru untuk menghapus tabel database. Berguna untuk membuat migrasi *rollback* jika Anda perlu mengembalikan perubahan skema database.
*   **Parameter**:
    *   `<nama_tabel>`: Nama tabel yang ingin Anda hapus.
*   **Contoh**: `.\layvx buat:hapus_tabel products`
*   **Output**: Akan membuat file di `database/tabel/` dengan format `YYYY_MM_DD_HHMMSS_drop_nama_tabel_table.php`. Fungsi `up()` di migrasi ini akan menghapus tabel.

### `.\layvx migrasi`

*   **Deskripsi**: Menjalankan semua migrasi database yang tertunda. Ini akan membuat, memodifikasi, atau menghapus tabel di database Anda sesuai dengan file migrasi yang belum dijalankan.
*   **Fitur Baru**: Secara otomatis akan membuat database yang ditentukan di konfigurasi Anda jika database tersebut belum ada di server MySQL/MariaDB.
*   **Contoh**: `.\layvx migrasi`
*   **Output**: Menampilkan daftar migrasi yang dijalankan dan statusnya.

### `.\layvx buat:model <NamaModel> [-t]`

*   **Deskripsi**: Membuat kelas Model baru di `app/Models/`. Model adalah representasi tabel database Anda dan berfungsi sebagai antarmuka untuk berinteraksi dengan data tersebut menggunakan ORM.
*   **Parameter**:
    *   `<NamaModel>`: Nama model yang ingin Anda buat (misalnya `User`, `Product`). Nama akan otomatis dikonversi ke format PascalCase (contoh: `user` menjadi `User.php`).
    *   `-t` (opsional): Jika disertakan, perintah ini juga akan membuat file migrasi (`buat:tabel`) yang sesuai untuk model tersebut.
*   **Contoh**: `.\layvx buat:model Post` atau `.\layvx buat:model Category -t`
*   **Output**: Membuat file `NamaModel.php` di `app/Models/`.

### `.\layvx buat:controller <NamaController>`

*   **Deskripsi**: Membuat kelas Controller baru di `app/Controllers/`. Controller menangani logika permintaan HTTP dan berfungsi sebagai jembatan antara Model dan View.
*   **Parameter**:
    *   `<NamaController>`: Nama controller yang ingin Anda buat (misalnya `Home`, `Auth`). Nama akan otomatis dikonversi ke format PascalCase dan ditambahkan sufiks `Controller` (contoh: `home` menjadi `HomeController.php`).
*   **Contoh**: `.\layvx buat:controller Welcome`
*   **Output**: Membuat file `NamaController.php` di `app/Controllers/`.

### `.\layvx cache:clear`

*   **Deskripsi**: Menghapus semua file cache view yang dihasilkan oleh framework. Ini berguna saat Anda membuat perubahan pada file tampilan (`.php` di folder `views/`) dan perubahan tersebut tidak segera terlihat di browser.
*   **Contoh**: `.\layvx cache:clear`
*   **Output**: Memberi tahu berapa banyak file cache yang telah dihapus.

### `.\layvx help`

*   **Deskripsi**: Menampilkan daftar semua perintah `layvx` yang tersedia beserta deskripsinya.
*   **Contoh**: `.\layvx help`
*   **Output**: Menampilkan pesan bantuan seperti yang Anda lihat sekarang.

## 4. Sistem Routing (`routes/web.php`)

Sistem routing adalah jantung dari bagaimana aplikasi Anda merespons permintaan HTTP dari pengguna. Ini adalah mekanisme yang memetakan URL yang diminta oleh pengguna ke kode Controller yang sesuai dalam aplikasi Anda.

### a. Konsep Routing

*   **Pemetaan URL ke Aksi**: Routing bertanggung jawab untuk "menerjemahkan" URL (misalnya `/dashboard` atau `/courses/123`) menjadi panggilan ke metode spesifik di dalam Controller Anda.
*   **Pemisahan Tanggung Jawab**: Dengan routing, Anda memisahkan logika aplikasi (Controller) dari cara URL distrukturkan, membuat aplikasi Anda lebih terorganisir dan mudah dipelihara.
*   **Front Controller (`public/index.php`)**: Di LayVX, semua permintaan masuk melalui satu titik masuk utama, yaitu `public/index.php`. File inilah yang bertanggung jawab untuk:
    1.  Memuat konfigurasi dan helper awal (`app/Core/Env.php`, `app/Core/ErrorHandler.php`, `app/Core/helpers.php`).
    2.  Memulai sesi PHP (`session_start()`).
    3.  Menginisialisasi token CSRF (`$_SESSION['tuama_token']`).
    4.  Memuat definisi rute dari `routes/web.php`.
    5.  Mendapatkan URI yang diminta dan metode HTTP.
    6.  Mencocokkan URI dengan rute yang terdaftar.
    7.  Memanggil Controller dan metode yang sesuai.
    8.  Menangani error 404 jika rute tidak ditemukan.

### b. Peran `routes/web.php` dan Helper Routing Global (`app/Core/helpers.php`)

File `routes/web.php` adalah tempat utama di mana Anda mendefinisikan semua rute web untuk aplikasi Anda. Ini adalah *repository* sentral di mana Anda mendeklarasikan bagaimana aplikasi Anda akan merespons berbagai permintaan URL.

Fitur routing LayVX sangat bergantung pada **fungsi helper global** yang didefinisikan di `app/Core/helpers.php` dan sebuah kelas pembangun rute (`RouteBuilder`).

*   **Fungsi Global `get()` dan `post()`**:
    *   Didefinisikan di `app/Core/helpers.php` menggunakan `if (!function_exists('get')) { ... }` dan `if (!function_exists('post')) { ... }`.
    *   Fungsi-fungsi ini menginisialisasi dan mengembalikan instance dari `RouteBuilder`.
    *   `get($uri, $action)`: Mendaftarkan rute yang merespons permintaan HTTP GET.
    *   `post($uri, $action)`: Mendaftarkan rute yang merespons permintaan HTTP POST.
*   **Kelas `RouteBuilder`**:
    *   Didefinisikan di `app/Core/helpers.php`.
    *   Setelah `get()` atau `post()` dipanggil, `RouteBuilder` bertanggung jawab untuk segera menambahkan rute ke array global `$routes` (yang didefinisikan di `app/Core/helpers.php` dan digunakan oleh `public/index.php`).
    *   Ia juga menyediakan metode `name()` untuk *method chaining*.
*   **Metode `name()`**:
    *   Didefinisikan dalam kelas `RouteBuilder`.
    *   Memungkinkan Anda memberikan nama unik untuk setiap rute (misalnya `->name('home')`).
    *   Nama rute disimpan dalam array global `$namedRoutes` (juga didefinisikan di `app/Core/helpers.php`). Nama rute berguna untuk menghasilkan URL di aplikasi Anda tanpa harus menuliskannya secara manual, membuat URL lebih mudah diubah di masa mendatang.
*   **Fungsi `route()` Helper**:
    *   Didefinisikan di `app/Core/helpers.php`.
    *   `route(string $name, array $params = [])`: Fungsi ini digunakan di dalam View atau Controller Anda untuk menghasilkan URL berdasarkan nama rute yang sudah didefinisikan.

*   **`$uri`**: Ini adalah string URL yang akan dicocokkan (misalnya `/login`, `/dashboard`). Router saat ini melakukan pencocokan string *persis*. **Penting: Implementasi router saat ini tidak mendukung parameter dinamis di URL (misalnya `/courses/{id}`).**
*   **`$action`**: Ini adalah array dalam format `['NamaController', 'NamaMetode']`.
    *   `NamaController`: Nama kelas Controller (misalnya `AuthController`, `CourseController`) yang berada di direktori `app/Controllers/`.
    *   `NamaMetode`: Nama metode di dalam kelas Controller tersebut yang akan dieksekusi ketika rute ini cocok.

### c. Cara Kerja Routing secara Detil

1.  **Permintaan Masuk**: Pengguna mengakses URL di browser mereka.
2.  **`public/index.php` Sebagai Gerbang**: Server web mengarahkan permintaan ke `public/index.php`.
3.  **Inisialisasi Lingkungan**: `public/index.php` mengatur lingkungan aplikasi (memuat ENV, error handler, helper, memulai sesi).
4.  **Pemuatan Rute Global**: File `routes/web.php` di-`require`. Selama pemuatan ini, fungsi global `get()` dan `post()` dipanggil. Fungsi-fungsi ini akan mengisi array global `$routes` dengan semua definisi rute (URI, metode HTTP, aksi Controller, dan nama rute melalui `RouteBuilder`).
5.  **Pencocokan URL**: `public/index.php` mengambil URL yang diminta (`$_SERVER['REQUEST_URI']`) dan metode HTTP (`$_SERVER['REQUEST_METHOD']`). Kemudian, ia mengiterasi melalui array `$routes` yang sesuai dengan metode HTTP (`$routes[$method]`) dan mencari rute yang URL-nya cocok *persis* dengan URL yang diminta.
6.  **Eksekusi Aksi Controller**:
    *   Jika rute ditemukan, `public/index.php` mengekstrak `NamaController` dan `NamaMetode` dari array `$action` yang terkait dengan rute tersebut.
    *   File Controller (`app/Controllers/NamaController.php`) di-`require_once`.
    *   Sebuah instance dari `NamaController` dibuat.
    *   Metode `NamaMetode` dipanggil pada instance Controller tersebut.
7.  **Respons Aplikasi**: Controller melakukan tugasnya (misalnya, mengambil data dari model, memproses input) dan biasanya memanggil fungsi `render()` (dari `helpers.php`) untuk menampilkan View kepada pengguna.
8.  **Penanganan 404**: Jika tidak ada rute yang cocok ditemukan untuk URI dan metode yang diminta, `public/index.php` akan mengembalikan kode status HTTP 404 dan menampilkan halaman error 404 dari `app/Core/errors/404.php`.

### d. Contoh `routes/web.php` (Disematkan di dalam helper global)

```php
<?php

// routes/web.php
// (Fungsi global get(), post(), dan name() didefinisikan di app/Core/helpers.php)

// Auth Routes
get('/', ['AuthController', 'showLogin'])->name('home');
get('/login', ['AuthController', 'showLogin'])->name('login');
post('/login/process', ['AuthController', 'handleLogin'])->name('login.process');
get('/logout', ['AuthController', 'logout'])->name('logout');

// Registration routes
get('/register/student', ['AuthController', 'showStudentRegisterForm'])->name('register.student');
post('/register/student/process', ['AuthController', 'registerStudent'])->name('register.student.process');

// ... dan seterusnya untuk rute-rute lainnya.
```

Dengan pemahaman ini, Anda dapat secara efektif mendefinisikan dan mengelola alur permintaan di LayVX Framework.