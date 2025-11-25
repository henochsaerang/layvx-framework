# Panduan Deployment ke Rumahweb (cPanel)

Dokumen ini menjelaskan cara mengunggah dan mengkonfigurasi proyek LMS ini di penyedia hosting yang menggunakan cPanel, seperti Rumahweb.

## Latar Belakang

Pada hosting cPanel, Anda dapat mengatur "Document Root" domain Anda untuk menunjuk langsung ke folder `public/` dari proyek Anda. Ini adalah praktik terbaik untuk keamanan dan struktur aplikasi MVC.

## Langkah 1: Struktur Direktori Ideal di Server

Tujuan kita adalah menempatkan sebagian besar file proyek Anda di luar folder `public_html` (yang merupakan Document Root default cPanel), dan kemudian mengarahkan domain Anda ke folder `public/` di dalam proyek Anda.

```
/home/user/  (Ini adalah root akun hosting Anda)
├── public_html/  <-- Document Root default cPanel (akan kita abaikan atau kosongkan)
│
├── your_project_folder/  <-- Folder proyek Anda (misal: LMSprojek)
│   ├── public/           <-- Document Root yang akan kita gunakan
│   │   ├── index.php
│   │   └── assets/
│   │
│   ├── app/
│   ├── config/
│   ├── routes/
│   ├── views/
│   ├── .env              <-- File konfigurasi SANGAT PENTING
│   └── layvx
│
└── ... folder/file lain di root hosting
```

## Langkah 2: Proses Unggah (Upload)

1.  **Buat Folder Proyek**: Menggunakan File Manager cPanel atau FTP client (seperti FileZilla), buat sebuah folder baru di **root** akun hosting Anda (misalnya `/home/user/LMSprojek`).
2.  **Unggah Semua File Proyek**: Unggah **seluruh isi** proyek lokal Anda (semua folder seperti `app/`, `config/`, `routes/`, `views/`, `public/`, serta file `.env` dan `layvx`) ke dalam folder baru yang Anda buat (`/home/user/LMSprojek/`).

## Langkah 3: Konfigurasi Domain (Document Root) di cPanel

Ini adalah langkah krusial untuk mengarahkan domain Anda ke folder `public/` proyek.

1.  **Masuk ke cPanel**: Login ke akun cPanel Rumahweb Anda.
2.  **Cari "Domains"**: Di bagian "Domains", cari opsi seperti "Domains", "Addon Domains", atau "Subdomains" (tergantung bagaimana domain Anda diatur).
3.  **Edit Document Root**:
    *   Temukan domain yang ingin Anda gunakan untuk aplikasi ini.
    *   Klik "Manage" atau ikon edit di samping domain tersebut.
    *   Cari kolom atau opsi untuk **"Document Root"** atau **"Web Root"**.
    *   Ubah path yang ada menjadi path lengkap ke folder `public/` di dalam proyek Anda. Contoh:
        *   Jika proyek Anda di `/home/user/LMSprojek/`, maka Document Root baru adalah `/home/user/LMSprojek/public`.
    *   Simpan perubahan.

## Langkah 4: Konfigurasi Penting Lainnya

1.  **Buat Database & Impor SQL**:
    *   Di cPanel, masuk ke bagian "Databases" > "MySQL Databases".
    *   Buat database baru, user database baru, dan berikan semua hak akses user tersebut ke database. Catat **nama database, username, password, dan host** (biasanya `localhost`).
    *   Buka **phpMyAdmin** (dari cPanel), pilih database yang baru Anda buat, dan impor file `weblms.sql` Anda.

2.  **Edit File `.env`**:
    *   Buka File Manager cPanel, navigasikan ke folder proyek Anda (`/home/user/LMSprojek/`).
    *   Edit file `.env` yang sudah Anda unggah.
    *   Isi dengan kredensial database yang Anda dapatkan dari langkah sebelumnya.
    *   **PENTING**: Ubah `APP_ENV` menjadi `production`.

    Contoh isi file `.env` di server:
    ```
    APP_ENV=production

    DB_HOST=localhost # <-- Host dari cPanel (biasanya localhost)
    DB_DATABASE=nama_db_anda # <-- Nama database dari cPanel
    DB_USERNAME=user_db_anda # <-- Username database dari cPanel
    DB_PASSWORD=PasswordKuatAnda
    ```

3.  **Versi PHP**:
    *   Di cPanel, cari "Select PHP Version" atau "MultiPHP Manager".
    *   Pastikan versi PHP yang digunakan adalah PHP 7.4 atau lebih baru.

## Selesai!

Dengan mengikuti langkah-langkah ini, aplikasi Anda akan berjalan dengan benar dan aman di Rumahweb, dengan struktur file yang optimal untuk aplikasi MVC.
