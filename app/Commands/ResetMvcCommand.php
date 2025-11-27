<?php

namespace App\Commands;

use App\Core\Command;

class ResetMvcCommand extends Command {
    protected $signature = 'buat:reset_mvc';
    protected $description = 'Menghapus dan membuat ulang (reset) semua direktori struktur MVC ke kondisi awal.';

    public function handle(array $args = []) {
        echo "Starting MVC structure reset...\n";

        // 1. Hapus Struktur
        $deleteCommand = new DeleteMvcCommand();
        $deleteCommand->handle($args);
        
        // 2. Buat Ulang Struktur
        $makeCommand = new MakeMvcCommand();
        $makeCommand->handle($args);

        echo "\nMVC structure successfully reset to default minimal state.\n";
    }
}