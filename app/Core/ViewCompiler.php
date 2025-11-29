<?php

namespace App\Core;

use Exception;

class ViewCompiler
{
    protected $layout = null;
    protected $sections = [];
    protected $compilers = [
        'Php', 'Echos', 'If', 'Foreach', 'For', 'While', 'Comments',
    ];

    /**
     * Get the path to the compiled version of a view.
     *
     * @param string $viewPath
     * @return string
     * @throws Exception
     */
    public function getCompiledPath(string $viewPath): string
    {
        $cachePath = $this->getCachePath($viewPath);

        // Smart Recompile: Check if the original view is newer than the cached one.
        if (file_exists($cachePath) && filemtime($viewPath) <= filemtime($cachePath)) {
            return $cachePath;
        }

        // Perform the compilation and cache the result.
        $content = $this->compile($viewPath);
        file_put_contents($cachePath, $content, LOCK_EX);

        return $cachePath;
    }

    /**
     * Get the path to the cache file for a given view.
     *
     * @param string $viewPath
     * @return string
     */
    protected function getCachePath(string $viewPath): string
    {
        $cacheDir = __DIR__ . '/../../storage/framework/views';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        return $cacheDir . '/' . sha1($viewPath) . '.php';
    }

    /**
     * Compile the view at the given path.
     *
     * @param string $viewPath
     * @return string
     * @throws Exception
     */
    public function compile(string $viewPath): string
    {
        if (!file_exists($viewPath)) {
            throw new Exception("View not found at path: {$viewPath}");
        }
        $content = file_get_contents($viewPath);

        // Basic Linting: Validate directive balance before compiling.
        $this->validateDirectiveBalance($content);
        
        // First pass: Handle layouts and sections.
        $content = $this->compileSections($content);
        $content = $this->compileExtends($content);

        if ($this->layout) {
            $layoutPath = $this->resolveViewPath($this->layout);
            $this->layout = null; // Reset for potential recursion
            $content = $this->compile($layoutPath);
        }

        // Second pass: Inject sections and compile other directives.
        $content = $this->compileYield($content);

        foreach ($this->compilers as $compiler) {
            $content = $this->{"compile{$compiler}"}($content);
        }

        return $content;
    }

    protected function validateDirectiveBalance(string $content)
    {
        $directives = ['section', 'if', 'foreach', 'for', 'while', 'php'];
        foreach ($directives as $d) {
            $startCount = substr_count($content, "@{$d}");
            $endCount = substr_count($content, "@end{$d}");
            if ($startCount !== $endCount) {
                throw new Exception("Directive balance error: '@{$d}' is not correctly closed with '@end{$d}'.");
            }
        }
    }
    
    protected function resolveViewPath(string $viewName): string
    {
        $basePath = __DIR__ . '/../../';
        return $basePath . 'views/' . str_replace('.', '/', $viewName) . '.php';
    }

    protected function compileExtends(string $content): string
    {
        $pattern = '/@extends\s*\(\s*\'(.*?)\'\s*\)/';
        if (preg_match($pattern, $content, $matches)) {
            $this->layout = $matches[1];
            return preg_replace($pattern, '', $content, 1);
        }
        return $content;
    }
    
    protected function compileSections(string $content): string
    {
        $pattern = '/@section\s*\(\s*\'(.*?)\'\s*\)(.*?)@endsection/s';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $this->sections[$match[1]] = $match[2];
        }

        return preg_replace($pattern, '', $content);
    }

    protected function compileYield(string $content): string
    {
        return preg_replace_callback('/@yield\s*\(\s*\'(.*?)\'\s*\)/', function ($matches) {
            return $this->sections[$matches[1]] ?? '';
        }, $content);
    }
    
    protected function compileComments(string $content): string
    {
        return preg_replace('/\{\{--(.+?)--\}\}/s', '', $content);
    }

    protected function compileEchos(string $content): string
    {
        $content = preg_replace_callback('/\{!!\s*(.+?)\s*!!\}/s', fn($m) => '<?php echo ' . $m[1] . '; ?>', $content);
        $content = preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/s', fn($m) => '<?php echo htmlspecialchars(' . $m[1] . ', ENT_QUOTES, \'UTF-8\'); ?>', $content);
        return $content;
    }
    
    protected function compilePhp(string $content): string
    {
        return preg_replace('/@php\s*(.*?)@endphp/s', '<?php $1 ?>', $content);
    }

    protected function compileIf(string $content): string
    {
        // Smart Regex for balanced parentheses
        $pattern = '/@(%s)\s*(?R)?(\((?:[^)(]+|(?2))*+\))/s';

        $content = preg_replace(sprintf($pattern, 'if'), '<?php if$2: ?>', $content);
        $content = preg_replace(sprintf($pattern, 'elseif'), '<?php elseif$2: ?>', $content);
        $content = preg_replace('/@else/', '<?php else: ?>', $content);
        $content = preg_replace('/@endif/', '<?php endif; ?>', $content);
        return $content;
    }

    protected function compileForeach(string $content): string
    {
        $pattern = '/@foreach\s*(\((?:[^)(]+|(?1))*+\))/s';
        return preg_replace([$pattern, '/@endforeach/'], ['<?php foreach$1: ?>', '<?php endforeach; ?>'], $content);
    }

    protected function compileFor(string $content): string
    {
        $pattern = '/@for\s*(\((?:[^)(]+|(?1))*+\))/s';
        return preg_replace([$pattern, '/@for/'], ['<?php for$1: ?>', '<?php endfor; ?>'], $content);
    }

    protected function compileWhile(string $content): string
    {
        $pattern = '/@while\s*(\((?:[^)(]+|(?1))*+\))/s';
        return preg_replace([$pattern, '/@while/'], ['<?php while$1: ?>', '<?php endwhile; ?>'], $content);
    }
}