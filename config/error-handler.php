<?php
/**
 * MTS Al-Ihsan - Comprehensive Error Handler
 * Menangkap semua error dan exception untuk debugging
 */

// Enable error display hanya untuk development
$isDevelopment = getenv('APP_ENV') === 'development' || getenv('APP_DEBUG') === 'true';

if ($isDevelopment) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
}

// Error log directory
$errorLogDir = __DIR__ . '/../logs';
if (!is_dir($errorLogDir)) {
    @mkdir($errorLogDir, 0755, true);
}
$errorLogFile = $errorLogDir . '/error.log';
ini_set('error_log', $errorLogFile);

// Custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $errorType = [
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated',
    ];

    $type = $errorType[$errno] ?? 'Unknown Error';
    $message = sprintf(
        "[%s] %s in %s on line %d",
        date('Y-m-d H:i:s'),
        $type,
        $errfile,
        $errline
    );

    // Log ke file
    error_log($message . "\nMessage: " . $errstr . "\n" . str_repeat('-', 80) . "\n");

    // Display ke development
    if (getenv('APP_DEBUG') === 'true') {
        echo sprintf(
            "<div style='background: #fee; padding: 15px; margin: 10px 0; border-left: 4px solid #c00;'>"
            . "<strong>%s:</strong> %s<br>"
            . "<small>File: %s:%d</small>"
            . "</div>",
            htmlspecialchars($type),
            htmlspecialchars($errstr),
            htmlspecialchars($errfile),
            $errline
        );
    }

    // Return true untuk indicate error telah ditangani
    return true;
});

// Custom exception handler
set_exception_handler(function(Throwable $e) {
    $message = sprintf(
        "[%s] Exception: %s in %s on line %d\nMessage: %s\nStack trace:\n%s\n%s\n",
        date('Y-m-d H:i:s'),
        get_class($e),
        $e->getFile(),
        $e->getLine(),
        $e->getMessage(),
        $e->getTraceAsString(),
        str_repeat('-', 80)
    );

    // Log ke file
    error_log($message);

    // Display ke development
    if (getenv('APP_DEBUG') === 'true') {
        echo sprintf(
            "<div style='background: #fee; padding: 15px; margin: 10px 0; border: 2px solid #c00;'>"
            . "<h3 style='color: #c00; margin-top: 0;'>%s</h3>"
            . "<p><strong>Message:</strong> %s</p>"
            . "<p><strong>File:</strong> %s:%d</p>"
            . "<h4>Stack Trace:</h4>"
            . "<pre style='background: #fff; padding: 10px; overflow-x: auto;'>%s</pre>"
            . "</div>",
            htmlspecialchars(get_class($e)),
            htmlspecialchars($e->getMessage()),
            htmlspecialchars($e->getFile()),
            $e->getLine(),
            htmlspecialchars($e->getTraceAsString())
        );
    } else {
        // Show generic error to user
        http_response_code(500);
        echo sprintf(
            "<div style='text-align: center; padding: 50px; font-family: Arial, sans-serif;'>"
            . "<h1 style='color: #c00;'>500 - Internal Server Error</h1>"
            . "<p>Terjadi kesalahan pada server. Administrator telah diberitahu.</p>"
            . "<p><small>Error ID: %s | Time: %s</small></p>"
            . "</div>",
            substr(md5($message), 0, 8),
            date('Y-m-d H:i:s')
        );
    }

    exit(1);
});

// Shutdown handler untuk fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $message = sprintf(
            "[%s] Fatal Error: %s in %s on line %d\n%s\n",
            date('Y-m-d H:i:s'),
            $error['message'],
            $error['file'],
            $error['line'],
            str_repeat('-', 80)
        );

        error_log($message);

        if (getenv('APP_DEBUG') === 'true') {
            echo sprintf(
                "<div style='background: #fee; padding: 15px; margin: 10px 0; border: 3px solid #900;'>"
                . "<h3 style='color: #900; margin-top: 0;'>Fatal Error</h3>"
                . "<p><strong>%s</strong></p>"
                . "<small>File: %s:%d</small>"
                . "</div>",
                htmlspecialchars($error['message']),
                htmlspecialchars($error['file']),
                $error['line']
            );
        } else {
            http_response_code(500);
            echo sprintf(
                "<div style='text-align: center; padding: 50px; font-family: Arial, sans-serif;'>"
                . "<h1 style='color: #c00;'>500 - Internal Server Error</h1>"
                . "<p>Terjadi kesalahan pada server. Administrator telah diberitahu.</p>"
                . "</div>"
            );
        }
    }
});

?>
