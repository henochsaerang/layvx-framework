<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Error</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f1f5f9; color: #1f2937; margin: 0; padding: 2rem; }
        .container { max-width: 1280px; margin: 0 auto; }
        .header { background-color: #dc2626; color: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; font-size: 1.875rem; }
        .header p { margin: 0.25rem 0 0; opacity: 0.9; font-family: 'Courier New', monospace; }
        .card { background-color: white; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { margin: 0; font-size: 1.125rem; font-weight: 600; }
        .card-header-file { font-family: 'Courier New', monospace; font-size: 0.875rem; color: #4b5563; }
        .code-snippet { background-color: #1e293b; color: #cbd5e1; font-family: 'SF Mono', 'Courier New', monospace; font-size: 0.875rem; overflow-x: auto; }
        .code-snippet pre { margin: 0; }
        .line { display: flex; padding: 0.1rem 1.5rem; }
        .line-number { min-width: 40px; color: #64748b; text-align: right; padding-right: 1rem; user-select: none; }
        .line-code { flex-grow: 1; }
        .line-highlight { background-color: rgba(220, 38, 38, 0.3); }
        .line-highlight .line-number { color: #f87171; }
        .footer { text-align: center; margin-top: 2rem; color: #6b7280; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="container">
        <?php
            $trace = $exception->getTrace();
            $main_error_frame = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'function' => '[Global Scope]',
            ];
            // Combine the main error with the rest of the trace
            array_unshift($trace, $main_error_frame);
        ?>

        <div class="header">
            <h1><?php echo htmlspecialchars($exception->getMessage()); ?></h1>
            <p><?php echo str_replace(dirname(__DIR__, 2) . '\\', '', $exception->getFile()); ?>:<?php echo $exception->getLine(); ?></p>
        </div>

        <?php foreach ($trace as $index => $frame):
            // Check if 'file' and 'line' keys exist before accessing them
            if (isset($frame['file']) && isset($frame['line'])):
        ?>
                <div class="card">
                    <div class="card-header">
                        <h2>#<?php echo count($trace) - $index -1; ?> <?php echo htmlspecialchars($frame['function'] ?? 'Unknown Function'); ?>()</h2>
                        <span class="card-header-file"><?php echo htmlspecialchars(str_replace(dirname(__DIR__, 2) . '\\', '', $frame['file'])); ?>:<?php echo $frame['line']; ?></span>
                    </div>
                    <div class="code-snippet">
                        <pre><code><?php
                            // Ensure the file exists before attempting to read it
                            if (file_exists($frame['file'])) {
                                $lines = file($frame['file']);
                                $start = max(0, $frame['line'] - 6);
                                $end = min(count($lines), $frame['line'] + 5);

                                for ($i = $start; $i < $end; $i++):
                                    $is_highlighted = ($i + 1) == $frame['line'];
                        ?><div class="line <?php echo $is_highlighted ? 'line-highlight' : ''; ?>">
    <span class="line-number"><?php echo $i + 1; ?></span>
    <span class="line-code"><?php echo htmlspecialchars($lines[$i]); ?></span>
</div><?php endfor; 
                            } // end if file_exists
                        ?></code></pre>
                    </div>
                </div>
            <?php endif; // end if isset file and line
        endforeach; ?>
    </div>

    <div class="footer">
        LAYVX (By Henoch A Saerang)
    </div>
</body>
</html>