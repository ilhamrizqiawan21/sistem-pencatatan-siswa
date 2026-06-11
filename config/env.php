<?php
// Simple .env loader (no external dependency)
// Loads .env in project root or config/.env into environment variables
function load_dotenv($paths = []) {
    $paths[] = __DIR__ . '/../.env';
    $paths[] = __DIR__ . '/.env';
    foreach ($paths as $p) {
        if (file_exists($p) && is_readable($p)) {
            $lines = file($p, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || strpos($line, '#') === 0) continue;
                // KEY=VALUE, support quoted values
                if (!strpos($line, '=')) continue;
                list($key, $val) = explode('=', $line, 2);
                $key = trim($key);
                $val = trim($val);
                if ((substr($val,0,1) === '"' && substr($val,-1) === '"') || (substr($val,0,1) === "'" && substr($val,-1) === "'")) {
                    $val = substr($val,1,-1);
                }
                // interpolate simple ${VAR} patterns
                $val = preg_replace_callback('/\$\{([A-Z0-9_]+)\}/i', function($m){
                    return getenv($m[1]) ?: $m[1];
                }, $val);
                if (getenv($key) === false) {
                    putenv("$key=$val");
                    $_ENV[$key] = $val;
                    $_SERVER[$key] = $val;
                }
            }
            return true;
        }
    }
    return false;
}

// Auto-load on include
load_dotenv();

?>