# Panduan Deployment ke InfinityFree

Dokumen ini menjelaskan cara mengunggah dan mengkonfigurasi proyek LMS ini di penyedia hosting gratis seperti InfinityFree yang tidak mengizinkan perubahan Document Root.

## Latar Belakang

Secara default, InfinityFree menggunakan folder `htdocs/` sebagai Document Root. Kita tidak bisa mengubahnya untuk menunjuk ke folder `public/` proyek kita. Oleh karena itu, kita perlu melakukan penyesuaian struktur saat mengunggah file.

## Langkah 1: Struktur Direktori di Server

Tujuan kita adalah mencapai struktur file seperti ini di server hosting Anda:

```
/ (Root Akun Hosting Anda, misal: /home/volX_Y/htdocs/yourdomain.com/)
├── htdocs/      <-- Folder web publik yang bisa diakses dari internet
│   ├── index.php
│   └── assets/
│
├── app/
├── config/
├── routes/
├── views/
├── .env         <-- File konfigurasi SANGAT PENTING
└── layvx
```

Perhatikan bagaimana hanya isi dari folder `public/` kita yang berada di dalam `htdocs/`.

## Langkah 2: Proses Unggah (Upload)

1.  **Unggah Folder Inti**: Menggunakan File Manager atau FTP client (seperti FileZilla), unggah folder-folder berikut ke direktori **root** akun hosting Anda (di luar `htdocs/`):
    *   `app/`
    *   `config/`
    *   `routes/`
    *   `views/`
    *   `layvx`

2.  **Unggah Isi Folder `public`**:
    *   Buka folder `public/` di komputer **lokal** Anda.
    *   Unggah **semua isinya** (yaitu file `index.php` dan folder `assets/`) ke dalam folder **`htdocs/`** di server hosting Anda.

## Langkah 3: Konfigurasi di Server

1.  **Buat Database**: Masuk ke Control Panel InfinityFree Anda, cari bagian database, buat database MySQL baru, dan catat **nama database, username, password, dan host** yang diberikan.

2.  **Impor SQL**: Buka **phpMyAdmin** dari Control Panel, pilih database yang baru Anda buat, dan impor file `weblms.sql` Anda.

3.  **Buat dan Isi File `.env`**:
    *   Di direktori **root** hosting Anda (di luar `htdocs/`), buat file baru bernama `.env`.
    *   Isi file tersebut dengan kredensial database dari langkah 1.
    *   **PENTING**: Ubah `APP_ENV` menjadi `production`.

    Contoh isi file `.env` di server:
    ```
    APP_ENV=production

    DB_HOST=sqlXXX.epizy.com  # <-- Host dari InfinityFree
    DB_DATABASE=epiz_XXXXXXXX_weblms # <-- Nama DB dari InfinityFree
    DB_USERNAME=epiz_XXXXXXXX # <-- Username dari InfinityFree
    DB_PASSWORD=PasswordKuatAnda
    ```

## Selesai!

Dengan mengikuti langkah-langkah ini, aplikasi Anda akan berjalan dengan benar dan aman di InfinityFree, karena kode inti dan file konfigurasi Anda berada di luar jangkauan akses publik.
