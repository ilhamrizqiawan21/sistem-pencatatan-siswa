<?php
/**
 * MTS Al-Ihsan - Debug & Error Logs Viewer
 * HANYA UNTUK DEVELOPMENT
 * 
 * Access: /mts-alihsan/debug/logs.php
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

// Get log file
$logFile = __DIR__ . '/../logs/error.log';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTS Al-Ihsan - Debug Logs</title>
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
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .info-box {
            background: #f0f7ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .info-box strong {
            color: #1976D2;
        }
        .controls {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-danger {
            background: #f44336;
            color: white;
        }
        .btn-danger:hover {
            background: #da190b;
        }
        .btn-secondary {
            background: #9e9e9e;
            color: white;
        }
        .btn-secondary:hover {
            background: #757575;
        }
        .logs-container {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 5px;
            max-height: 600px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.6;
        }
        .logs-container .error {
            color: #f48771;
        }
        .logs-container .warning {
            color: #dcdcaa;
        }
        .logs-container .info {
            color: #9cdcfe;
        }
        .no-logs {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .footer {
            background: #f5f5f5;
            padding: 15px 30px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🐛 Debug Logs Viewer</h1>
            <p>MTS Al-Ihsan - Error & Debug Information</p>
        </div>

        <div class="content">
            <div class="info-box">
                <strong>ℹ️ Mode Development Aktif</strong><br>
                Error handler sedang aktif dan mencatat semua error ke dalam log file.
                Halaman ini hanya tersedia dalam mode development.
            </div>

            <div class="controls">
                <button class="btn-primary" onclick="location.reload()">🔄 Refresh</button>
                <button class="btn-secondary" onclick="scrollToBottom()">⬇️ Scroll ke Bawah</button>
                <?php if (file_exists($logFile) && filesize($logFile) > 0): ?>
                    <button class="btn-danger" onclick="clearLogs()">🗑️ Hapus Logs</button>
                <?php endif; ?>
                <button class="btn-secondary" onclick="window.close()">✕ Tutup</button>
            </div>

            <div class="logs-container" id="logsContainer">
                <?php
                if (file_exists($logFile) && filesize($logFile) > 0) {
                    $logs = file_get_contents($logFile);
                    $lines = explode("\n", $logs);
                    
                    // Show last 100 lines
                    $lines = array_slice($lines, -100);
                    
                    foreach ($lines as $line) {
                        if (empty($line)) continue;
                        
                        $class = 'info';
                        if (strpos($line, 'Error') !== false || strpos($line, 'Exception') !== false) {
                            $class = 'error';
                        } elseif (strpos($line, 'Warning') !== false) {
                            $class = 'warning';
                        }
                        
                        echo sprintf(
                            '<div class="%s">%s</div>',
                            htmlspecialchars($class),
                            htmlspecialchars($line)
                        );
                    }
                } else {
                    echo '<div class="no-logs">📋 Belum ada log. Semua sistem berjalan normal!</div>';
                }
                ?>
            </div>
        </div>

        <div class="footer">
            <strong>Info:</strong>
            <?php
            if (file_exists($logFile)) {
                $size = filesize($logFile);
                $modified = filemtime($logFile);
                printf(
                    'Log file: %s | Size: %s | Last modified: %s',
                    $logFile,
                    formatBytes($size),
                    date('Y-m-d H:i:s', $modified)
                );
            } else {
                echo 'Log file belum dibuat';
            }
            ?>
        </div>
    </div>

    <script>
        function scrollToBottom() {
            const container = document.getElementById('logsContainer');
            container.scrollTop = container.scrollHeight;
        }

        function clearLogs() {
            if (confirm('Yakin ingin menghapus semua logs?')) {
                fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=clear'
                }).then(() => {
                    location.reload();
                });
            }
        }

        // Auto scroll to bottom on load
        window.addEventListener('load', scrollToBottom);
    </script>
</body>
</html>

<?php

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Handle clear logs request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear') {
    if (file_exists($logFile)) {
        file_put_contents($logFile, '');
        echo 'Logs cleared';
    }
    exit;
}
?>
