<?php

namespace App\Commands;

use App\Core\Command;

class HelpCommand extends Command {
    protected $signature = 'help';
    protected $description = 'Displays help information.';

    public function handle(array $args = []) {
        echo "\n";
        echo "\033[1;34mLayVX Framework\033[0m (By Henoch A Saerang) - CLI Tool\n";
        echo "--------------------------------------------------------\n";
        echo "Penggunaan: php layvx/layvx <command> [opsi]\n\n";

        echo "\033[1;33mAvailable Commands:\033[0m\n\n";

        $this->printGroup('Structure Presets (Scaffolding)', [
            'buat:mvc'      => 'Membuat struktur MVC standar.',
            'buat:adr'      => 'Membuat struktur ADR (Action-Domain-Responder).',
            'buat:ddd'      => 'Membuat struktur DDD (Domain-Driven Design).',
            'buat:hmvc'     => 'Membuat struktur Modular/HMVC.',
            'buat:minimal'  => 'Membuat struktur Minimal (Microservice).',
        ]);

        $this->printGroup('Structure Management', [
            'buat:hapus_mvc'     => 'Menghapus struktur MVC.',
            'buat:hapus_adr'     => 'Menghapus struktur ADR.',
            'buat:hapus_ddd'     => 'Menghapus struktur DDD.',
            'buat:hapus_hmvc'    => 'Menghapus struktur HMVC.',
            'buat:hapus_minimal' => 'Menghapus struktur Minimal.',
        ]);

        $this->printGroup('Generators', [
            'buat:controller <Nama>' => 'Membuat Controller baru.',
            'buat:model <Nama> [-t]' => 'Membuat Model baru (-t untuk +migrasi).',
            'buat:view <nama.view>'  => 'Membuat View baru (bisa dot notation).',
            'buat:middleware <Nama>' => 'Membuat Middleware baru.',
            'buat:modul <Nama>'      => 'Membuat Modul baru (khusus HMVC).',
        ]);

        $this->printGroup('Database & Queue', [
            'migrasi'                => 'Menjalankan migrasi database.',
            'buat:tabel <nama>'      => 'Membuat migrasi tabel baru.',
            'buat:sql'               => 'Backup database ke file SQL.',
            'buat:jobs'              => 'Membuat tabel jobs untuk antrean (Queue).',
            'queue:work'             => 'Menjalankan worker untuk memproses antrean.',
            'buat:hapus_tabel <nama>'=> 'Membuat migrasi drop tabel.',
        ]);

        $this->printGroup('Desktop & Mobile App', [
            'buat:exe'       => 'Build aplikasi menjadi Desktop App portable.',
            'buat:hapus_exe' => 'Menghapus hasil build desktop.',
            'buat:pwa'       => 'Konfigurasi Mobile PWA (Manifest, Icon, Service Worker).',
        ]);

        $this->printGroup('General & Testing', [
            'serve'          => 'Menjalankan server development PHP.',
            'test'           => 'Menjalankan unit testing aplikasi.',
            'self:update'    => 'Update framework ke versi terbaru (GitHub).',
            'cache:clear'    => 'Menghapus cache view.',
            'help'           => 'Menampilkan bantuan ini.',
        ]);
        
        echo "\n";
    }

    /**
     * Helper untuk mencetak grup command dengan rapi.
     */
    private function printGroup($title, $commands) {
        echo "\033[32m" . $title . "\033[0m\n";
        foreach ($commands as $command => $desc) {
            // Padding agar deskripsi rata kanan
            printf("  %-30s %s\n", $command, $desc);
        }
        echo "\n";
    }
}