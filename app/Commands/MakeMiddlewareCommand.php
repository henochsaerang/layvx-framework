<?php

namespace App\Commands;

use App\Core\Command;

class MakeMiddlewareCommand extends Command {
    protected $signature = 'buat:middleware';
    protected $description = 'Membuat class Middleware baru.';

    public function handle(array $args = []) {
        $middlewareName = $args[0] ?? null;
        if (empty($middlewareName)) {
            echo "Error: Please provide a name for the middleware.\n";
            echo "Usage: php layvx buat:middleware <MiddlewareName>\n";
            exit(1);
        }

        $middlewareName = ucfirst($middlewareName);
        $basePath = __DIR__ . '/../../';
        $directory = $basePath . 'app/Middleware';

        // Check if directory exists, create if not
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                echo "Error: Failed to create directory {$directory}.\n";
                exit(1);
            }
            echo "Directory created: {$directory}\n";
        }

        $filePath = $directory . '/' . $middlewareName . '.php';

        if (file_exists($filePath)) {
            echo "Error: Middleware {$middlewareName} already exists.\n";
            exit(1);
        }

        $stub = <<<PHP
<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use Closure;

class {$middlewareName} implements Middleware {
    /**
     * Handle an incoming request.
     *
     * @param  \App\Core\Request  \$request
     * @param  \Closure  \$next
     * @return mixed
     */
    public function handle(Request \$request, Closure \$next) {
        // Logika Middleware SEBELUM request mencapai Controller
        // Contoh: Cek otentikasi
        
        // if (!\$request->user()) {
        //     return Response::redirect('/login');
        // }

        // Meneruskan request ke lapisan berikutnya
        \$response = \$next(\$request);

        // Logika Middleware SETELAH Controller merespons
        
        return \$response;
    }
}

PHP;

        if (file_put_contents($filePath, $stub) === false) {
            echo "Error: Could not create middleware file.\n";
            exit(1);
        }
        echo "Middleware created successfully: {$filePath}\n";
    }
}