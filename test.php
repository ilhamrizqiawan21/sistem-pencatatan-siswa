<?php
/**
 * Simple test to check if routing is working
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>MTS Al-Ihsan - Routing Test</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .status { padding: 15px; margin: 10px 0; border-left: 4px solid; border-radius: 4px; }
        .success { background: #e8f5e9; border-left-color: #4caf50; color: #2e7d32; }
        .error { background: #ffebee; border-left-color: #f44336; color: #c62828; }
        .warning { background: #fff3e0; border-left-color: #ff9800; color: #e65100; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th { background: #667eea; color: white; padding: 10px; text-align: left; }
        table td { padding: 10px; border-bottom: 1px solid #ddd; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 MTS Al-Ihsan - Routing Diagnostic Test</h1>";

// Test 1: PHP Version
$phpVersion = phpversion();
$phpOk = version_compare($phpVersion, '7.4.0', '>=');
echo "<div class='status " . ($phpOk ? 'success' : 'warning') . "'>
    <strong>PHP Version:</strong> $phpVersion " . ($phpOk ? '✓' : '⚠') . "
</div>";

// Test 2: Request Variables
echo "<div class='status success'>
    <strong>Request Information:</strong><br>
    REQUEST_URI: <code>" . htmlspecialchars($_SERVER['REQUEST_URI']) . "</code><br>
    REQUEST_METHOD: <code>" . htmlspecialchars($_SERVER['REQUEST_METHOD']) . "</code><br>
    DOCUMENT_ROOT: <code>" . htmlspecialchars($_SERVER['DOCUMENT_ROOT']) . "</code>
</div>";

// Test 3: File/Directory Existence
echo "<h2>📁 File/Directory Checks</h2>";
$files = [
    'config/env.php' => __DIR__ . '/config/env.php',
    'config/error-handler.php' => __DIR__ . '/config/error-handler.php',
    'config/db.php' => __DIR__ . '/config/db.php',
    'config/auth.php' => __DIR__ . '/config/auth.php',
    'config/functions.php' => __DIR__ . '/config/functions.php',
    'config/router.php' => __DIR__ . '/config/router.php',
    'includes/header.php' => __DIR__ . '/includes/header.php',
    '.env' => __DIR__ . '/.env',
];

echo "<table>";
echo "<tr><th>File</th><th>Status</th></tr>";
foreach ($files as $name => $path) {
    $exists = file_exists($path);
    $status = $exists ? '✓ Exists' : '✗ Missing';
    $class = $exists ? 'success' : 'error';
    echo "<tr><td><code>$name</code></td><td class='$class'>$status</td></tr>";
}
echo "</table>";

// Test 4: Load Configuration
echo "<h2>⚙️ Configuration Test</h2>";
try {
    require_once 'config/env.php';
    
    $baseUrl = getenv('BASE_URL');
    $appEnv = getenv('APP_ENV');
    $appDebug = getenv('APP_DEBUG');
    
    echo "<div class='status success'>
        <strong>Environment Variables Loaded:</strong><br>
        BASE_URL: <code>" . htmlspecialchars($baseUrl) . "</code><br>
        APP_ENV: <code>" . htmlspecialchars($appEnv) . "</code><br>
        APP_DEBUG: <code>" . htmlspecialchars($appDebug) . "</code>
    </div>";
} catch (Exception $e) {
    echo "<div class='status error'>
        <strong>Error loading env:</strong> " . htmlspecialchars($e->getMessage()) . "
    </div>";
}

// Test 5: Error Handler
echo "<h2>🛡️ Error Handler Test</h2>";
try {
    require_once 'config/error-handler.php';
    echo "<div class='status success'>
        <strong>✓ Error handler loaded successfully</strong>
    </div>";
} catch (Exception $e) {
    echo "<div class='status error'>
        <strong>✗ Error handler failed:</strong> " . htmlspecialchars($e->getMessage()) . "
    </div>";
}

// Test 6: Router
echo "<h2>🚀 Router Test</h2>";
try {
    require_once 'config/auth.php';
    require_once 'config/functions.php';
    require_once 'config/router.php';
    
    $page = $router->getPage();
    $module = $router->getModule();
    $uri = $router->getUri();
    
    echo "<div class='status success'>
        <strong>✓ Router loaded successfully</strong><br>
        URI: <code>" . htmlspecialchars($uri) . "</code><br>
        Page: <code>" . htmlspecialchars($page) . "</code><br>
        Module: <code>" . htmlspecialchars($module ?: 'none') . "</code>
    </div>";
} catch (Exception $e) {
    echo "<div class='status error'>
        <strong>✗ Router failed:</strong> " . htmlspecialchars($e->getMessage()) . "<br>
        <small>File: " . htmlspecialchars($e->getFile()) . " Line: " . $e->getLine() . "</small>
    </div>";
}

// Test 7: Session
echo "<h2>🔐 Session Test</h2>";
try {
    $sessionId = session_id();
    $sessionName = session_name();
    $sessionStatus = session_status() === PHP_SESSION_ACTIVE ? '✓ Active' : '✗ Inactive';
    
    echo "<div class='status success'>
        <strong>Session Status:</strong> $sessionStatus<br>
        Session Name: <code>$sessionName</code><br>
        Session ID: <code>" . htmlspecialchars(substr($sessionId, 0, 20)) . "...</code>
    </div>";
} catch (Exception $e) {
    echo "<div class='status error'>
        <strong>Session error:</strong> " . htmlspecialchars($e->getMessage()) . "
    </div>";
}

echo "</div>
</body>
</html>";
?>
