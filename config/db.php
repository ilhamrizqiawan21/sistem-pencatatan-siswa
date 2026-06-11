<?php
// Database configuration — prefer environment variables for secrets
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'mts_alihsan';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: 'Hash2856@';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // disable emulated prepares to improve security
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Log detailed error for admin and show generic message to user
    if (function_exists('error_log')) {
        error_log('Database connection error: ' . $e->getMessage());
    }
    die('Koneksi database gagal. Silakan hubungi administrator.');
}
?>