<?php

namespace App\Commands;

use App\Core\Command;

class HelpCommand extends Command {
    protected $signature = 'help';
    protected $description = 'Displays help information.';

    public function handle(array $args = []) {
        echo "LayVX (By Henoch A Saerang) Console Tool\n\n";
        echo "Usage:\n";
        echo "  .\layvx <command>\n\n";
        echo "Available commands:\n";
        echo "  buat:mvc            Membuat semua direktori struktur MVC (Models, Controllers, Routes, dll.).\n";
        echo "  buat:hapus_mvc      Menghapus semua direktori struktur MVC ke kondisi minimal.\n";
        echo "  buat:reset_mvc      Menghapus dan membuat ulang (reset) semua direktori struktur MVC.\n";
        echo "  serve               Serve the application on the PHP development server\n";
        echo "  buat:tabel <nama>   Membuat file migrasi untuk membuat tabel baru\n";
        echo "  buat:hapus_tabel <nama> Membuat file migrasi untuk menghapus tabel\n";
        echo "  migrasi             Menjalankan migrasi database yang tertunda\n";
        echo "  buat:model <Nama>   Membuat class Model baru. Gunakan flag -t untuk membuat migrasi juga.\n";
        echo "  buat:controller <Nama> Membuat class Controller baru.\n";
        echo "  buat:middleware <Nama> Membuat class Middleware baru.\n";
        echo "  buat:view <Nama>    Membuat file View baru. Gunakan notasi titik (dot notation) untuk sub-direktori.\n"; // Perintah baru
        echo "  cache:clear         Menghapus semua file cache view.\n";
    }
}