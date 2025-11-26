# Tutorial Framework: Perintah CLI (Command Line Interface)

Framework ini dilengkapi dengan tool command-line sederhana bernama `layvx` untuk membantu mempercepat pengembangan. Semua perintah dijalankan dari direktori root proyek Anda menggunakan `layvx [nama_perintah]`.

## Daftar Perintah

Berikut adalah daftar perintah yang tersedia:

### `serve`
Menjalankan server pengembangan PHP bawaan.
```bash
layvx serve
```
Secara default, server akan berjalan di `http://localhost:8000`. Anda bisa mengubah port dengan memberikan argumen:
```bash
layvx serve --port=8080
```

---

### `migrasi`
Menjalankan migrasi database yang masih tertunda. Perintah ini akan mengeksekusi method `up()` pada semua file migrasi di `database/tabel` yang belum pernah dijalankan.
```bash
layvx migrasi
```
Status migrasi dilacak di dalam tabel `migrations` pada database Anda.

---

### `buat:controller`
Membuat file Controller baru di direktori `app/Controllers`.
```bash
layvx buat:controller NamaController
```
Contoh: `layvx buat:controller CourseController` akan membuat file `app/Controllers/CourseController.php`.

---

### `buat:model`
Membuat file Model baru di direktori `app/Models`.
```bash
layvx buat:model NamaModel
```
Contoh: `layvx buat:model Enrollment` akan membuat file `app/Models/Enrollment.php`.

---

### `buat:tabel`
Membuat file migrasi baru di direktori `database/tabel` untuk **membuat** sebuah tabel.
```bash
layvx buat:tabel create_nama_tabel_table
```
Contoh: `layvx buat:tabel create_assignments_table` akan membuat file migrasi dengan nama seperti `2025_11_26_000000_create_assignments_table.php`.

---

### `cache:clear`
Menghapus semua file *cache* view yang telah dikompilasi oleh *template engine*. File-file ini berada di `storage/framework/views`.
```bash
layvx cache:clear
```
Perintah ini berguna jika Anda melakukan perubahan besar pada file-file view atau layout dan ingin memastikan perubahan tersebut langsung terlihat tanpa ada sisa-sisa cache.

---

### `help`
Menampilkan daftar semua perintah yang tersedia beserta deskripsinya.
```bash
layvx help
```
Atau cukup jalankan `layvx` tanpa argumen apa pun.
