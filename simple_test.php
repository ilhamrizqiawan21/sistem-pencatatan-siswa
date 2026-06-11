<?php
// Simple test without routing
// Access: https://ilham.didzacorp.com/mts-alihsan/simple_test.php

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head><title>Simple Test</title>";
echo "<style>body { font-family: Arial; padding: 20px; } .ok { color: green; } .error { color: red; }</style>";
echo "</head>";
echo "<body>";
echo "<h1>Simple PHP Test - No Routing</h1>";

// Test 1: PHP is working
echo "<p class='ok'>✓ PHP is working</p>";

// Test 2: Check files
$files = [
    'config/env.php',
    'config/error-handler.php',
    'config/constants.php',
    'config/db.php'
];

echo "<h2>File Check:</h2>";
foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    $exists = file_exists($path);
    echo "<p class='" . ($exists ? 'ok' : 'error') . "'>" . ($exists ? '✓' : '✗') . " $file</p>";
}

// Test 3: Load env
echo "<h2>Environment Variables:</h2>";
try {
    require_once 'config/env.php';
    $baseUrl = getenv('BASE_URL');
    $appEnv = getenv('APP_ENV');
    echo "<p class='ok'>✓ BASE_URL: $baseUrl</p>";
    echo "<p class='ok'>✓ APP_ENV: $appEnv</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test 4: Load error handler
echo "<h2>Error Handler:</h2>";
try {
    require_once 'config/error-handler.php';
    echo "<p class='ok'>✓ Error handler loaded</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test 5: Database
echo "<h2>Database Connection:</h2>";
try {
    require_once 'config/constants.php';
    require_once 'config/db.php';
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "<p class='ok'>✓ Database connected. Users count: $count</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test 6: Session
echo "<h2>Session:</h2>";
echo "<p class='ok'>✓ Session status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "</p>";

echo "</body></html>";
?>
