# LayVX Framework

![LayVX Banner](https://via.placeholder.com/800x200?text=LayVX+Framework)

**LayVX** adalah Framework PHP revolusioner dengan filosofi **"Zero Dependency"** (Tanpa Composer). Framework ini dirancang untuk menjadi sangat fleksibel, ringan, namun memiliki fitur setara framework enterprise.

LayVX mampu mengubah kode Anda menjadi **Web**, **Aplikasi Desktop (.exe)**, hingga **Aplikasi Mobile (PWA)** hanya dengan satu baris perintah.

---

### 👨‍💻 Tentang Pengembang

Framework ini dikembangkan dengan ❤️ dan ☕ oleh:

> **Henoch Saerang** > Mahasiswa Semester 5, Teknik Informatika  
> Universitas Negeri Manado (UNIMA)

---

## 🚀 Mengapa LayVX?

* **Zero Dependency:** Tidak butuh `composer install`. Download dan langsung jalan di mana saja.
* **Multi-Platform:** Satu basis kode untuk Web, Desktop (Windows), dan Mobile (Android).
* **Dynamic Scaffolding:** Bisa berubah wujud menjadi MVC, HMVC, ADR, DDD, atau Minimal API.
* **Enterprise Ready:** Dilengkapi Built-in Queue, Caching, Testing, dan Security.
* **Portable:** Sangat mudah dipindahkan antar server atau komputer tanpa konfigurasi rumit.

---

## 📦 Instalasi

1.  **Clone Repositori:**
    ```bash
    git clone [https://github.com/henochsaerang/layvx-framework](https://github.com/henochsaerang/layvx-framework) namaproject
    cd namaproject
    ```

2.  **Pilih Struktur (Preset):**
    Pilih arsitektur yang Anda inginkan (lihat bagian Dynamic Scaffolding).
    ```bash
    layvx buat:mvc
    ```

3.  **Setup Database:**
    Edit file `.env` dan jalankan migrasi.
    ```bash
    layvx migrasi
    ```

4.  **Jalankan:**
    ```bash
    layvx serve
    ```

---

## 🛠️ Dynamic Scaffolding (Arsitektur Bunglon)

LayVX tidak memaksakan satu struktur. Anda bisa memilih "baju" untuk proyek Anda:

| Perintah | Deskripsi | Cocok Untuk |
| :--- | :--- | :--- |
| `layvx buat:mvc` | Struktur Standar (Controller, Model, View). | Web umum, Blog, Toko Online. |
| `layvx buat:hmvc` | Hierarchical MVC (Modular). | Aplikasi skala besar, Tim banyak. |
| `layvx buat:adr` | Action-Domain-Responder. | API-Centric App, Modern Web. |
| `layvx buat:ddd` | Domain-Driven Design. | Aplikasi Enterprise Kompleks. |
| `layvx buat:minimal` | Struktur mikro (Hanya Route). | Microservices, API Sederhana. |

*Ingin ganti struktur? Gunakan `layvx buat:hapus_mvc` (atau sesuai tipe) lalu buat yang baru.*

---

## 📱 Multi-Platform Build

Fitur andalan LayVX yang jarang dimiliki framework lain:

### 1. 🖥️ Build ke Desktop (.exe)
Mengubah web Anda menjadi aplikasi desktop portable dengan mode kiosk (tanpa address bar).
```bash
layvx buat:exe
```
*Hasil build ada di folder `build_desktop/`. Salin folder `php` ke dalamnya untuk membuatnya 100% portable tanpa instalasi.*

### 2. 📱 Build ke Mobile (PWA)
Mengonfigurasi manifest dan service worker agar web bisa diinstal di Android (Add to Home Screen).
```bash
layvx buat:pwa
```
*Otomatis men-generate icon aplikasi, manifest.json, dan halaman offline.*

---

## 🔥 Fitur Enterprise (Native)

Meski tanpa library luar, LayVX memiliki fitur canggih buatan sendiri:

### 🛡️ Keamanan (Security)
* **CSRF Protection:** Middleware otomatis mencegah serangan Cross-Site Request Forgery.
* **XSS Cleaning:** Sanitasi input otomatis lewat `$request->clean()`.
* **Custom Error Pages:** Tampilan cantik untuk error 404 dan 419 (Page Expired).

### 📨 Queue System (Antrean)
Proses tugas berat di latar belakang tanpa membebani user.
1.  Buat tabel jobs: `layvx buat:jobs` -> `layvx migrasi`
2.  Push job: `Queue::push(KirimEmailJob::class, $data)`
3.  Jalankan worker: `layvx queue:work`

### ⚡ Caching System
Simpan data berat ke file cache untuk performa kilat.
```php
$users = Cache::remember('all_users', 3600, function() {
    return User::all();
});
```

### 🧪 Automated Testing
Pastikan aplikasi stabil sebelum rilis.
```bash
layvx test
```

---

## 📖 CLI Command Cheat Sheet

Berikut adalah daftar perintah `layvx` yang bisa Anda gunakan:

**Generator:**
* `buat:controller <Nama>`
* `buat:model <Nama> -t` (dengan migrasi)
* `buat:view <nama.view>`
* `buat:modul <Nama>` (Khusus HMVC)
* `buat:middleware <Nama>`

**Database:**
* `buat:tabel <nama>`
* `migrasi`
* `buat:jobs` (Tabel Queue)

**Utility:**
* `serve` (Jalankan Server)
* `cache:clear` (Hapus Cache View)
* `buat:hapus_exe` (Hapus build desktop)

---

## 📄 Lisensi

LayVX adalah software open-source di bawah lisensi **MIT**.

---
*Copyright © 2025 Henoch Saerang. All Rights Reserved.*