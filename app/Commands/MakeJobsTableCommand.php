<?php

namespace App\Commands;

use App\Core\Command;

class MakeJobsTableCommand extends Command
{
    protected $signature = 'buat:jobs';
    protected $description = 'Membuat file migrasi untuk tabel queue jobs.';

    public function handle(array $args = [])
    {
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_create_jobs_table.php";
        $path = 'database/tabel/' . $filename;

        $content = $this->getMigrationContent('CreateJobsTable');

        if (file_put_contents($path, $content) === false) {
            echo "Error: Gagal membuat file migrasi.\n";
            return 1;
        }

        echo "Migrasi tabel jobs berhasil dibuat: {$path}\n";
        return 0;
    }

    protected function getMigrationContent(string $className): string
    {
        return <<<PHP
<?php

use App\Core\Migration;
use App\Core\Column;

class {$className} extends Migration
{
    public function up()
    {
        $this->createTable('jobs', [
            (new Column('id'))->id(),
            (new Column('queue'))->string()->notNullable()->default('default'),
            (new Column('payload'))->text()->notNullable(), // Use TEXT for longtext/json
            (new Column('attempts'))->integer()->notNullable()->default(0), // No tinyInt, use integer
            (new Column('available_at'))->timestamp()->nullable(),
            (new Column('created_at'))->timestamp()->notNullable()->default('CURRENT_TIMESTAMP')
        ]);
    }

    public function down()
    {
        $this->dropTable('jobs');
    }
}
PHP;
    }
}

