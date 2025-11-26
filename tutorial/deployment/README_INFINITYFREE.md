# Panduan Deployment ke Shared Hosting (InfinityFree)

Deployment ke shared hosting seperti InfinityFree memerlukan penyesuaian struktur direktori karena kita biasanya tidak bisa mengubah *document root*. Panduan ini akan menjelaskan cara melakukannya dengan aman.

Tujuannya adalah agar hanya file dari direktori `public/` yang bisa diakses publik, sementara sisa file framework (seperti `app`, `config`, `.env`) berada di luar jangkauan web.

## Langkah 1: Restrukturisasi Direktori di Komputer Lokal

Sebelum mengunggah, kita akan mengatur ulang struktur file.

1.  Buat sebuah folder baru di komputer Anda, misalnya `UPLOAD_KE_HOSTING`.
2.  **Pindahkan isi `public/`**: Salin **semua isi** dari direktori `public/` framework Anda (termasuk `index.php`, `.htaccess`, folder `assets`, dan folder `uploads`) ke dalam folder `UPLOAD_KE_HOSTING`.
3.  **Buat Subdirektori untuk Core Framework**: Di dalam `UPLOAD_KE_HOSTING`, buat folder baru, misalnya bernama `layvx_core`.
4.  **Pindahkan Sisa File**: Pindahkan semua file dan folder lain dari root framework Anda (seperti `app`, `config`, `database`, `routes`, `storage`, `views`, `layvx`, `.env`, dll.) ke dalam folder `layvx_core` yang baru saja Anda buat.

Struktur folder `UPLOAD_KE_HOSTING` Anda sekarang akan terlihat seperti ini:
```
UPLOAD_KE_HOSTING/
├── .htaccess
├── index.php
├── assets/
├── uploads/
└── layvx_core/
    ├── app/
    ├── config/
    ├── database/
    ├── routes/
    ├── storage/
    ├── .env
    └── ... (semua file dan folder lainnya)
```

## Langkah 2: Edit `index.php`

File `index.php` yang sekarang ada di root `UPLOAD_KE_HOSTING` perlu diedit agar bisa menemukan file-file framework di lokasi barunya (`layvx_core`).

Buka `index.php` dan ubah path-nya. Ganti semua `__DIR__ . '/..'` menjadi `__DIR__ . '/layvx_core'`.

**Contoh Perubahan:**

Ganti baris-baris ini:
```php
// require_once '../app/Core/Env.php';
// \App\Core\Env::load(__DIR__ . '/..');
// require_once '../app/Core/autoloader.php';
// $providers = require __DIR__ . '/../config/app.php';
// ... dan seterusnya
```

Menjadi seperti ini:
```php
// require_once './layvx_core/app/Core/Env.php'; // Atau gunakan path absolut
\App\Core\Env::load(__DIR__ . '/layvx_core');
require_once __DIR__ . '/layvx_core/app/Core/autoloader.php';
$providers = require __DIR__ . '/layvx_core/config/app.php';
// Lakukan hal yang sama untuk semua path lainnya di file ini.
// Contoh untuk 'routes/web.php':
// \App\Core\Route::load('../routes/web.php');
// menjadi:
\App\Core\Route::load(__DIR__ . '/layvx_core/routes/web.php');

```
**Penting:** Pastikan semua path yang merujuk ke direktori di luar `public` (menggunakan `../`) diperbarui untuk menunjuk ke dalam `layvx_core/`.

## Langkah 3: Buat File `.htaccess`

File `.htaccess` ini sangat penting. File ini akan mengarahkan semua request ke `index.php`, kecuali request tersebut adalah untuk file atau direktori yang benar-benar ada (seperti gambar di folder `assets/` atau `uploads/`).

Buat file bernama `.htaccess` di dalam `UPLOAD_KE_HOSTING` (di samping `index.php`) dengan konten berikut:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Alihkan semua request ke index.php jika file/folder tidak ditemukan
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>
```

## Langkah 4: Upload ke InfinityFree

1.  Login ke cPanel InfinityFree Anda dan buka File Manager.
2.  Masuk ke direktori `htdocs/`.
3.  Upload **semua isi** dari folder `UPLOAD_KE_HOSTING` ke dalam direktori `htdocs/`. Anda bisa melakukannya dengan meng-upload file ZIP lalu mengekstraknya di File Manager.
4.  **Konfigurasi Database**: Buat database MySQL dan user baru dari cPanel InfinityFree.
5.  **Update `.env`**: Edit file `.env` (yang sekarang ada di `htdocs/layvx_core/.env`) melalui File Manager. Masukkan nama database, username, dan password yang Anda dapatkan dari cPanel. Host database biasanya bukan `localhost`, cek detailnya di cPanel (seringkali seperti `sqlXXX.epizy.com`).

## Langkah 5: Akses dan Penyimpanan Gambar

-   **Akses Gambar**: Dengan struktur ini, gambar yang ada di `public/uploads/` (sekarang di `htdocs/uploads/`) bisa diakses langsung melalui URL, contoh: `http://namadomainanda.com/uploads/namafile.jpg`.
-   **Penyimpanan Gambar**: Pastikan logika file upload di aplikasi Anda menyimpan file ke direktori `uploads/`. Karena `index.php` sekarang berada di root, path relatifnya adalah `'uploads/' . $namaFile`. Pastikan direktori `uploads` di server memiliki izin tulis (biasanya 755 atau 777, namun periksa dokumentasi hosting Anda).

Dengan mengikuti langkah-langkah ini, aplikasi Anda akan berjalan di InfinityFree dengan struktur yang lebih aman, meniru lingkungan produksi di mana hanya direktori `public` yang terekspos.