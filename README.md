Ini adalah versi `README.md` yang telah diperbaiki dan diformat dengan struktur Markdown yang jelas, berdasarkan konten revisi yang Anda berikan serta konsisten dengan struktur framework LayVX:

````markdown
# LayVX Framework

LayVX adalah sebuah **PHP framework MVC** (Model-View-Controller) ringan yang dirancang untuk pengembangan aplikasi web yang cepat dan terstruktur. Framework ini mendukung instalasi **Minimal Setup** melalui alat CLI yang efisien.

---

## Fitur Utama

* **Arsitektur MVC**: Menyediakan pemisahan yang jelas antara logika bisnis, presentasi, dan data.
* **Service Container & Dependency Injection**: Manajemen dependensi yang kuat untuk kode yang lebih modular dan mudah diuji.
* **Middleware Pipeline**: Sistem middleware yang fleksibel untuk memproses request HTTP sebelum dan sesudah mencapai controller.
* **Manajemen Sesi Abstrak**: Class `App\Core\Session` untuk pengelolaan sesi yang aman dan fleksibel.
* **ORM (Object-Relational Mapping)**: Interaksi database berbasis objek dengan pemetaan Model-ke-Tabel dan dukungan relasi dasar.
* **Templating Engine**: Sintaks templating mirip Blade untuk tampilan yang bersih dan dinamis (lihat `App\Core\ViewCompiler.php`).
* **Command Line Interface (CLI)**: Alat bantu `layvx` untuk mengotomatisasi tugas-tugas umum seperti membuat controller, model, dan menjalankan migrasi.

---

## Dokumentasi Lengkap

Anda dapat menemukan panduan penggunaan, penjelasan fitur, dan contoh kode yang lebih rinci di dalam folder `tutorial/`.

---

## Instalasi & Setup Cepat (Minimal Setup)

LayVX mendukung instalasi minimalis. Setelah mengklon repositori, Anda dapat menggunakan perintah `buat:mvc` untuk membuat semua folder struktur aplikasi yang diperlukan.

### 1. Clone Repositori & Persiapan
```bash
git clone [https://github.com/henochsaerang/layvx-framework](https://github.com/henochsaerang/layvx-framework) namaproject
cd namaproject
````

### 2\. Buat Struktur MVC Otomatis

Perintah ini akan membuat semua folder yang hilang (`public`, `routes`, `views`, `app/Controllers`, `app/Models`, dll.) serta file bootstrap penting (`index.php`, `web.php`, `AppServiceProvider.php`).

```bash
layvx buat:mvc
```

### 3\. Konfigurasi Environment

Isi detail koneksi database dan pengaturan aplikasi lainnya pada file `.env` di root proyek Anda.

```env
APP_ENV=development
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=layvx_db
DB_USERNAME=root
DB_PASSWORD=
```

### 4\. Setup Database

Buat database dengan nama yang sesuai dengan konfigurasi Anda (e.g., `layvx_db`). Jalankan migrasi untuk membuat tabel:

```bash
layvx migrasi
```

-----

## Penggunaan Dasar

### Menjalankan Server Pengembangan

Gunakan perintah `serve` untuk menjalankan aplikasi pada server pengembangan internal PHP.

```bash
layvx serve
```

Aplikasi Anda akan dapat diakses di `http://127.0.0.1:8000`.

-----

## Command Line Interface (CLI)

LayVX CLI (`layvx`) menyediakan perintah untuk manajemen kode dan struktur:

| Perintah | Deskripsi |
| :--- | :--- |
| `buat:mvc` | Membuat semua direktori struktur MVC yang hilang. |
| `buat:reset_mvc` | Menghapus dan membuat ulang (reset) semua direktori struktur MVC. |
| `serve` | Menjalankan server pengembangan PHP. |
| `buat:controller <Nama>` | Membuat class Controller baru di `app/Controllers`. |
| `buat:model <Nama> -t` | Membuat class Model baru di `app/Models` (gunakan `-t` untuk membuat migrasi juga). |
| `buat:middleware <Nama>` | Membuat class Middleware baru di `app/Middleware`. |
| `buat:view <Nama>` | Membuat file View baru di `views/` (mendukung dot notation, e.g., `auth.login`). |
| `buat:tabel <nama>` | Membuat file migrasi baru untuk membuat tabel. |
| `migrasi` | Menjalankan migrasi database yang tertunda. |
| `cache:clear` | Menghapus semua file cache view yang dikompilasi. |

-----

## Lisensi

Proyek ini dilisensikan di bawah [MIT License](https://www.google.com/search?q=./LICENSE).

```
```