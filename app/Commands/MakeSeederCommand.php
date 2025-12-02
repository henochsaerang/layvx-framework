<?php

namespace App\Commands;

use App\Core\Command;

class MakeSeederCommand extends Command
{
    protected $signature = 'buat:seeder <NamaSeeder>';
    protected $description = 'Buat file seeder baru.';

    public function handle()
    {
        $seederName = $this->input->getArgument('NamaSeeder');
        $directory = 'database/seeders';
        $path = "{$directory}/{$seederName}.php";

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $stub = <<<'EOT'
<?php

use App\Core\Seeder;
// use App\Models\YourModel;

class {NamaSeeder} extends Seeder
{
    public function run()
    {
        // Contoh:
        // $this->db->query("INSERT INTO users (name, email, password) VALUES ('Admin', 'admin@example.com', 'password')");
        
        // Atau menggunakan model jika sudah di-setup
        // YourModel::create([
        //     'field' => 'value',
        // ]);
    }
}
EOT;

        $content = str_replace('{NamaSeeder}', $seederName, $stub);

        if (file_exists($path)) {
            $this->error("Seeder {$seederName} sudah ada.");
            return;
        }

        file_put_contents($path, $content);
        $this->info("Seeder {$seederName} berhasil dibuat di {$path}");
    }
}
