# Panduan dan Praktik Terbaik untuk ORM LayVX

Dokumen ini menjelaskan beberapa konsep inti dan praktik terbaik saat bekerja dengan Model pada framework LayVX untuk menghindari masalah umum.

## Arsitektur Model: Magic Properties dan Array `attributes`

ORM (Object-Relational Mapper) pada LayVX menggunakan pendekatan yang umum di banyak framework PHP, yaitu *magic properties*.

Ketika Anda mengambil data dari database, misalnya menggunakan `User::find(1)`, data dari setiap kolom (`id`, `name`, `password`, dll.) tidak disimpan ke dalam properti publik yang dideklarasikan secara eksplisit di dalam kelas Model. Sebaliknya, semua data tersebut disimpan sebagai *key-value pair* di dalam sebuah array internal (protected) bernama `$attributes`.

**Contoh Internal:**
```php
// Di dalam objek User yang diambil dari DB
$user->attributes = [
    'id' => 1,
    'name' => 'Nama User',
    'email' => 'user@example.com',
    'password' => '$2y$10$...' // Hash password
];
```

Ketika Anda mencoba mengakses data ini seolah-olah itu adalah properti biasa (misalnya `$user->password`), PHP secara otomatis memanggil *magic method* `__get($key)` yang ada di dalam kelas `Model` dasar. Method `__get` inilah yang kemudian mencari *key* `password` di dalam array `$attributes` dan mengembalikan nilainya.

## Masalah Umum: Jangan Mendeklarasikan Properti Publik!

Karena sistem ini, Anda **TIDAK BOLEH** mendeklarasikan properti yang namanya sama dengan kolom database sebagai `public` di dalam kelas Model Anda.

### Contoh yang SALAH (Akan Menyebabkan Error)

```php
// app/Models/User.php

class User extends Model 
{
    public $id;
    public $name;
    public $email;
    public $password; // <-- KESALAHAN DI SINI

    // ...
}
```

**Mengapa ini salah?**
Ketika Anda mendeklarasikan `public $password;`, PHP akan membuat properti riil pada objek tersebut dengan nilai default `NULL`. Saat Anda memanggil `$user->password`, PHP akan langsung mengakses properti publik ini dan mendapatkan `NULL`, **tanpa pernah memanggil *magic method* `__get`**. Akibatnya, Anda tidak akan pernah mendapatkan nilai hash password yang sebenarnya dari array `$attributes`, dan fungsi seperti `password_verify()` akan gagal.

## Praktik Terbaik: Gunakan Anotasi PHPDoc `@property`

Lalu, bagaimana cara memberitahu IDE (seperti VS Code atau PhpStorm) tentang properti-properti ini agar Anda mendapatkan *autocomplete* dan menghilangkan "error merah" (peringatan properti tidak terdefinisi)?

Gunakan komentar **PHPDoc** dengan tag `@property`.

### Contoh yang BENAR

```php
// app/Models/User.php

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string $nim_nip
 * @property string $created_at
 * @property string $updated_at
 */
class User extends Model 
{
    protected static $table = 'users';
    protected static $fillable = ['name', 'email', 'password', 'role', 'nim_nip'];

    // ...
}
```

Dengan cara ini:
1.  **IDE Anda "paham"**: IDE akan membaca anotasi `@property` dan mengenali bahwa objek `User` memiliki properti `password` bertipe `string`, sehingga tidak ada lagi peringatan "error merah".
2.  **Kode Anda berfungsi**: Karena tidak ada properti `public` yang dideklarasikan, pemanggilan `$user->password` akan tetap ditangani oleh *magic method* `__get` seperti yang seharusnya, dan Anda akan mendapatkan data yang benar dari database.

Dengan mengikuti panduan ini, Anda dapat bekerja dengan lancar tanpa mengorbankan fungsionalitas inti dari ORM LayVX.
