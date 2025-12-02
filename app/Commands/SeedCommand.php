<?php

namespace App\Commands;

use App\Core\Command;
use PDO;

class SeedCommand extends Command
{
    protected $signature = 'db:seed [class=DatabaseSeeder]';
    protected $description = 'Jalankan database seeder.';

    public function handle()
    {
        $seederClass = $this->input->getArgument('class');
        $seederDir = 'database/seeders';
        $masterSeederFile = "{$seederDir}/DatabaseSeeder.php";

        // Auto-init: Buat direktori dan master seeder jika belum ada
        if (!is_dir($seederDir)) {
            mkdir($seederDir, 0755, true);
        }

        if (!file_exists($masterSeederFile)) {
            $this->createDefaultDatabaseSeeder($masterSeederFile);
        }

        $seederFile = "{$seederDir}/{$seederClass}.php";

        if (!file_exists($seederFile)) {
            $this->error("File seeder tidak ditemukan: {$seederFile}");
            return;
        }

        require_once $seederFile;

        if (!class_exists($seederClass)) {
            $this->error("Class seeder tidak ditemukan di dalam file: {$seederClass}");
            return;
        }

        try {
            $pdo = $this->app->get(PDO::class);
            $seeder = new $seederClass($pdo);
            $seeder->run();
            $this->info("Seeding selesai: {$seederClass}");
        } catch (\Exception $e) {
            $this->error("Terjadi error saat seeding: " . $e->getMessage());
        }
    }

    private function createDefaultDatabaseSeeder($path)
    {
        $content = <<<'EOT'
<?php

use App\Core\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan semua seeder dari sini.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);
        // $this->call(ProductSeeder::class);
    }
}
EOT;
        file_put_contents($path, $content);
        $this->info("File default DatabaseSeeder.php berhasil dibuat.");
    }
}
