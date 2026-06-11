<?php
/**
 * MTS Al-Ihsan - Debug & System Information
 * HANYA UNTUK DEVELOPMENT
 */

// Load environment variables FIRST
require_once '../config/env.php';

// Load error handler
require_once '../config/error-handler.php';

// Check if debug mode is enabled
if (getenv('APP_DEBUG') !== 'true') {
    http_response_code(404);
    echo "404 Not Found";
    exit;
}


?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTS Al-Ihsan - Debug Info</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #667eea;
            font-size: 20px;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }
        .card {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        .card h3 {
            color: #333;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .card p {
            color: #666;
            font-size: 13px;
            word-break: break-all;
        }
        .status-good {
            background: #e8f5e9;
            border-left-color: #4caf50;
        }
        .status-good p {
            color: #2e7d32;
        }
        .status-warning {
            background: #fff3e0;
            border-left-color: #ff9800;
        }
        .status-warning p {
            color: #e65100;
        }
        .status-error {
            background: #ffebee;
            border-left-color: #f44336;
        }
        .status-error p {
            color: #c62828;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        table tr:hover {
            background: #f5f5f5;
        }
        .code {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
            margin-top: 10px;
        }
        .quick-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        a {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        a:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Debug Information</h1>
            <p>MTS Al-Ihsan - System & Environment Status</p>
        </div>

        <div class="content">
            <div class="warning">
                ⚠️ <strong>Development Mode Aktif</strong> - Halaman ini hanya tersedia untuk debugging dan diagnostik.
                Matikan di production!
            </div>

            <div class="section">
                <h2>🚀 Quick Links</h2>
                <div class="quick-links">
                    <a href="logs.php">📋 View Error Logs</a>
                    <a href="/mts-alihsan/">🏠 Back to App</a>
                </div>
            </div>

            <div class="section">
                <h2>📊 PHP Information</h2>
                <div class="grid">
                    <div class="card <?php echo phpversion() >= '7.4' ? 'status-good' : 'status-warning'; ?>">
                        <h3>PHP Version</h3>
                        <p><?php echo phpversion(); ?></p>
                    </div>
                    <div class="card status-good">
                        <h3>Server Software</h3>
                        <p><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
                    </div>
                    <div class="card status-good">
                        <h3>Server OS</h3>
                        <p><?php echo php_uname(); ?></p>
                    </div>
                    <div class="card <?php echo ini_get('display_errors') ? 'status-good' : 'status-warning'; ?>">
                        <h3>Display Errors</h3>
                        <p><?php echo ini_get('display_errors') ? 'ON ✓' : 'OFF'; ?></p>
                    </div>
                    <div class="card status-good">
                        <h3>Error Reporting</h3>
                        <p><?php echo ini_get('error_reporting'); ?></p>
                    </div>
                    <div class="card status-good">
                        <h3>Memory Limit</h3>
                        <p><?php echo ini_get('memory_limit'); ?></p>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2>🗄️ Database Information</h2>
                <div class="grid">
                    <div class="card status-good">
                        <h3>DB Host</h3>
                        <p><?php echo getenv('DB_HOST') ?: 'localhost'; ?></p>
                    </div>
                    <div class="card status-good">
                        <h3>DB Name</h3>
                        <p><?php echo getenv('DB_NAME') ?: 'mts_alihsan'; ?></p>
                    </div>
                    <div class="card status-good">
                        <h3>DB User</h3>
                        <p><?php echo getenv('DB_USER') ?: 'root'; ?></p>
                    </div>
                    <div class="card status-good">
                        <h3>PDO Drivers</h3>
                        <p><?php echo implode(', ', PDO::getAvailableDrivers()); ?></p>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2>📁 Extensions</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Extension</th>
                            <th>Status</th>
                            <th>Version</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $extensions = [
                            'pdo' => 'PDO',
                            'pdo_mysql' => 'PDO MySQL',
                            'json' => 'JSON',
                            'curl' => 'cURL',
                            'gd' => 'GD',
                            'zip' => 'ZIP',
                            'mbstring' => 'Multibyte String',
                        ];

                        foreach ($extensions as $ext => $name) {
                            $loaded = extension_loaded($ext);
                            $version = phpversion($ext);
                            $status = $loaded ? '<span style="color: #4caf50;">✓ Loaded</span>' : '<span style="color: #f44336;">✗ Not Loaded</span>';
                            $version = $version ?: 'N/A';
                            echo sprintf(
                                '<tr><td>%s</td><td>%s</td><td>%s</td></tr>',
                                $name,
                                $status,
                                $version
                            );
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <h2>🌍 Environment Variables</h2>
                <div class="code">
<?php
$env = getenv();
$appEnv = [
    'APP_ENV' => $env['APP_ENV'] ?? 'not set',
    'APP_DEBUG' => $env['APP_DEBUG'] ?? 'not set',
    'BASE_URL' => $env['BASE_URL'] ?? 'not set',
    'DB_HOST' => $env['DB_HOST'] ?? 'not set',
    'DB_NAME' => $env['DB_NAME'] ?? 'not set',
];

foreach ($appEnv as $key => $value) {
    echo sprintf("%s=%s\n", $key, htmlspecialchars($value));
}
?>
                </div>
            </div>

            <div class="section">
                <h2>📋 Server Information</h2>
                <table>
                    <tbody>
                        <tr>
                            <td><strong>Document Root</strong></td>
                            <td><?php echo $_SERVER['DOCUMENT_ROOT']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Script Filename</strong></td>
                            <td><?php echo $_SERVER['SCRIPT_FILENAME']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Request Method</strong></td>
                            <td><?php echo $_SERVER['REQUEST_METHOD']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Server Port</strong></td>
                            <td><?php echo $_SERVER['SERVER_PORT']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Remote Address</strong></td>
                            <td><?php echo $_SERVER['REMOTE_ADDR']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>HTTPS</strong></td>
                            <td><?php echo (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'YES ✓' : 'NO'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <h2>🗂️ File System</h2>
                <div class="grid">
                    <?php
                    $paths = [
                        'config' => '../config',
                        'includes' => '../includes',
                        'modules' => '../modules',
                        'uploads' => '../uploads',
                        'logs' => '../logs',
                    ];

                    foreach ($paths as $name => $path) {
                        $exists = is_dir($path);
                        $writable = $exists && is_writable($path);
                        $class = $writable ? 'status-good' : ($exists ? 'status-warning' : 'status-error');
                        echo sprintf(
                            '<div class="card %s"><h3>%s</h3><p>%s</p></div>',
                            $class,
                            ucfirst($name),
                            $writable ? '✓ Exists & Writable' : ($exists ? '⚠ Exists but Read-only' : '✗ Not Found')
                        );
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
