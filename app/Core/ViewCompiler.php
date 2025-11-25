<?php

namespace App\Core;

use Exception;

class ViewCompiler {
    protected $compilers = [
        'extends',
        'comments',
        'echos',
        'custom',
        'if',
        'foreach',
        'for',
        'while',
        'yield',
        'section',
    ];

    /**
     * The path to the layout view.
     * @var string|null
     */
    protected $layout = null;

    /**
     * The array of sections content.
     * @var array
     */
    protected $sections = [];

    /**
     * The number of active sections.
     * @var int
     */
    protected $sectionCount = 0;


    public function compile(string $viewPath): string {
        if (!file_exists($viewPath)) {
            throw new Exception("View not found at path: {$viewPath}");
        }
        $content = file_get_contents($viewPath);

        // Find and store sections, then remove them.
        $content = $this->compileSections($content);
        
        // Find a layout and remove the @extends directive.
        $content = $this->compileExtends($content);

        // If a layout is being used, the current $content is just leftover whitespace.
        // We need to recursively compile the layout file. The result of that
        // compilation will become our new content.
        if ($this->layout) {
            $layoutPath = $this->resolveViewPath($this->layout);
            $this->layout = null; // Reset for subsequent compiles in the same request
            $content = $this->compile($layoutPath);
        }
        
        // Now we have the final view content, with sections either stripped
        // (if it was a child view) or ready to be yielded into (if it's the layout).
        // We can now run the remaining compilers.
        
        // IMPORTANT: Yield must be compiled first, to inject section content.
        $content = $this->compileYield($content);
        
        // Now compile the rest on the fully assembled content.
        $content = $this->compileComments($content);
        $content = $this->compileEchos($content);
        $content = $this->compileCustom($content);
        $content = $this->compileIf($content);
        $content = $this->compileForeach($content);
        $content = $this->compileFor($content);
        $content = $this->compileWhile($content);
        
        return $content;
    }

    protected function resolveViewPath(string $viewName): string {
        $basePath = __DIR__ . '/../../';
        return $basePath . 'views/' . str_replace('.', '/', $viewName) . '.php';
    }

    protected function compileExtends(string $content): string {
        $pattern = '/@extends\s*\(\s*\'(.*?)\'\s*\)/';
        preg_match($pattern, $content, $matches);
        if ($matches) {
            $this->layout = $matches[1];
            // Remove the @extends directive from the content
            return preg_replace($pattern, '', $content, 1);
        }
        return $content;
    }
    
    protected function compileSections(string $content): string {
        $pattern = '/@section\s*\(\s*\'(.*?)\'\s*\)(.*?)@endsection/s';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $sectionName = $match[1];
            $sectionContent = $match[2];
            $this->sections[$sectionName] = $sectionContent;
        }

        // Remove all section blocks from the content
        return preg_replace($pattern, '', $content);
    }

    protected function compileYield(string $content): string {
        $pattern = '/@yield\s*\(\s*\'(.*?)\'\s*\)/';
        return preg_replace_callback($pattern, function ($matches) {
            $sectionName = $matches[1];
            // Return the stored section content, or an empty string if not found
            return $this->sections[$sectionName] ?? '';
        }, $content);
    }
    
    protected function compileComments(string $content): string {
        return preg_replace('/\{\{--(.+?)--\}\}/s', '', $content);
    }

    protected function compileEchos(string $content): string {
        $content = preg_replace_callback('/\{!!\s*(.+?)\s*!!\}/s', function ($matches) {
            return '<?php echo ' . $matches[1] . '; ?>';
        }, $content);
        $content = preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/s', function ($matches) {
            return '<?php echo htmlspecialchars(' . $matches[1] . ', ENT_QUOTES, \'UTF-8\'); ?>';
        }, $content);
        return $content;
    }

    protected function compileCustom(string $content): string {
        $content = preg_replace('/@tuama/', '<?php tuama_field(); ?>', $content);
        return $content;
    }

    protected function compileIf(string $content): string {
        $content = preg_replace('/@if\s*\((.*?)\)/', '<?php if ($1): ?>', $content);
        $content = preg_replace('/@elseif\s*\((.*?)\)/', '<?php elseif ($1): ?>', $content);
        $content = preg_replace('/@else/', '<?php else: ?>', $content);
        $content = preg_replace('/@endif/', '<?php endif; ?>', $content);
        return $content;
    }

    protected function compileForeach(string $content): string {
        $content = preg_replace('/@foreach\s*\((.*?)\)/', '<?php foreach ($1): ?>', $content);
        $content = preg_replace('/@endforeach/', '<?php endforeach; ?>', $content);
        return $content;
    }

    protected function compileFor(string $content): string {
        $content = preg_replace('/@for\s*\((.*?)\)/', '<?php for ($1): ?>', $content);
        $content = preg_replace('/@endfor/', '<?php endfor; ?>', $content);
        return $content;
    }

    protected function compileWhile(string $content): string {
        $content = preg_replace('/@while\s*\((.*?)\)/', '<?php while ($1): ?>', $content);
        $content = preg_replace('/@endwhile/', '<?php endwhile; ?>', $content);
        return $content;
    }
}
