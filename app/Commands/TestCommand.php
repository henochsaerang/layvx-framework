<?php

namespace App\Commands;

use App\Core\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

class TestCommand extends Command
{
    protected $signature = 'test';
    protected $description = 'Runs the application tests.';

    public function handle(array $args = [])
    {
        $testsDir = __DIR__ . '/../../tests';
        if (!is_dir($testsDir)) {
            mkdir($testsDir, 0755, true);
            echo "Created 'tests' directory. Please add your test files there.\n";
            return;
        }

        $passed = 0;
        $failed = 0;
        $totalTests = 0;

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testsDir));

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), 'Test.php')) {
                require_once $file->getPathname();
                $className = str_replace('.php', '', $file->getFilename());
                
                // This assumes no namespace for tests, which is simple for this runner.
                if (!class_exists($className, false)) {
                     echo "\n\033[33m[SKIPPED]\033[0m Class '{$className}' not found in {$file->getFilename()}\n";
                     continue;
                }

                $reflection = new \ReflectionClass($className);
                $testMethods = [];
                foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                    if (str_starts_with($method->getName(), 'test')) {
                        $testMethods[] = $method->getName();
                    }
                }

                if (empty($testMethods)) {
                    continue;
                }
                
                echo "\n\033[1;37mRunning tests from: {$className}\033[0m\n";

                $instance = new $className();

                foreach ($testMethods as $methodName) {
                    $totalTests++;
                    try {
                        $instance->$methodName();
                        $passed++;
                        echo "  \033[32m✓ PASSED\033[0m ... {$methodName}\n";
                    } catch (Throwable $e) {
                        $failed++;
                        echo "  \033[31m✗ FAILED\033[0m ... {$methodName}\n";
                        echo "    \033[90m" . $e->getMessage() . "\033[0m\n";
                    }
                }
            }
        }

        echo "\n-------------------\n";
        echo "Tests:  ";
        if ($failed > 0) {
            echo "\033[31m{$failed} failed, \033[0m";
        }
        if ($passed > 0) {
            echo "\033[32m{$passed} passed, \033[0m";
        }
        echo "{$totalTests} total.\n";
        
        return $failed > 0 ? 1 : 0;
    }
}
