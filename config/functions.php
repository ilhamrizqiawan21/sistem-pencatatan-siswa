<?php
function formatTanggal($tanggal, $format = 'd-m-Y') {
    return date($format, strtotime($tanggal));
}

function uploadFile($file, $targetDir, $allowedTypes = ['jpg','jpeg']) {
    // Basic upload hardening: check errors, size, MIME type (only JPEG), extension and create safe filename
    $maxSize = 5 * 1024 * 1024; // 5 MB
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['error' => 'Parameter upload tidak valid.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Terjadi kesalahan saat upload (kode: ' . $file['error'] . ').'];
    }
    if ($file['size'] > $maxSize) {
        return ['error' => 'File terlalu besar. Maksimum 5MB.'];
    }

    // Validate MIME type using finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Allow only jpeg mime and extensions jpg/jpeg
    $allowedExts = array_map('strtolower', $allowedTypes);
    if ($mime !== 'image/jpeg' || !in_array($ext, $allowedExts)) {
        return ['error' => 'Hanya file gambar berformat JPG atau JPEG yang diperbolehkan.'];
    }

    // Prepare destination
    $newFileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $targetDir = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR;
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0755, true)) {
            return ['error' => 'Gagal membuat direktori tujuan.'];
        }
    }
    $uploadPath = $targetDir . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['error' => 'Gagal mengupload file.'];
    }
    // Set safe permissions
    @chmod($uploadPath, 0644);

    return ['success' => true, 'filename' => $newFileName];
}

function getTahunAjaranAktif($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM tahun_ajaran WHERE is_aktif = 1 LIMIT 1");
    $stmt->execute();
    return $stmt->fetch();
}

function hitungPoinPelanggaran($pdo, $siswa_id, $tahun_ajaran_id) {
    $stmt = $pdo->prepare("SELECT SUM(jp.poin) as total_poin 
                            FROM pelanggaran p 
                            JOIN jenis_pelanggaran jp ON p.jenis_pelanggaran_id = jp.id 
                            WHERE p.siswa_id = ? AND p.tahun_ajaran_id = ?");
    $stmt->execute([$siswa_id, $tahun_ajaran_id]);
    $result = $stmt->fetch();
    return $result['total_poin'] ?? 0;
}

// CSRF helpers
function generate_csrf_token() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * ============================================
 * URL HELPER FUNCTIONS - Clean URL Generation
 * ============================================
 */

/**
 * Generate clean URL without .php extension
 * 
 * Examples:
 * url('dashboard') => /mts-alihsan/dashboard
 * url('login') => /mts-alihsan/login
 * url('modules/absensi') => /mts-alihsan/absensi
 * url('modules/kelas', ['id' => 5]) => /mts-alihsan/kelas?id=5
 */
function url($path = '', $params = []) {
    if (!defined('BASE_URL')) {
        return '';
    }

    // Normalize path
    $path = trim($path, '/');
    
    // Remove .php extension if present
    $path = str_replace('.php', '', $path);
    
    // Handle module paths: convert 'modules/absensi' to 'absensi'
    if (strpos($path, 'modules/') === 0) {
        $path = str_replace('modules/', '', $path);
    }
    
    // Build URL
    $url = BASE_URL;
    if (!empty($path) && $path !== 'index' && $path !== 'dashboard') {
        $url .= $path;
    }
    
    // Add query parameters
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    return $url;
}

/**
 * Check if current page is the given path
 */
function isCurrentPage($path) {
    $currentUri = $_SERVER['REQUEST_URI'];
    $path = trim($path, '/');
    $path = str_replace('modules/', '', $path);
    $path = str_replace('.php', '', $path);
    
    return strpos($currentUri, $path) !== false;
}

/**
 * Redirect to a clean URL
 */
function redirectTo($path, $params = []) {
    header('Location: ' . url($path, $params));
    exit;
}

/**
 * Generate a form action URL
 */
function formAction($module, $action = 'proses', $params = []) {
    $path = !empty($module) ? "modules/{$module}/{$action}.php" : $action . '.php';
    $path = str_replace('.php', '', $path);
    
    // Return direct path for forms (will still work with .htaccess)
    if (isset($params['method']) && strtoupper($params['method']) === 'POST') {
        unset($params['method']);
    }
    
    // For POST requests, use relative path
    return url($path, $params);
}

/**
 * Get clean URL for module pages
 */
function moduleUrl($moduleName, $action = '', $params = []) {
    $path = $moduleName;
    if (!empty($action) && $action !== 'index') {
        $path .= '/' . $action;
    }
    return url($path, $params);
}

/**
 * ============================================
 * FLASH MESSAGE FUNCTIONS
 * ============================================
 */

/**
 * Set a flash message in session
 * Types: success, error, warning, info
 */
function setFlash($message, $type = 'success') {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'message' => $message,
        'type'    => $type,
        'timestamp' => time()
    ];
}

/**
 * Get and clear flash message from session
 */
function getFlash() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Check if there's a flash message
 */
function hasFlash() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    return isset($_SESSION['flash']);
}

/**
 * Render flash message HTML
 */
function renderFlashMessage($flash) {
    if (!$flash) return '';
    
    $type = $flash['type'] ?? 'success';
    $message = htmlspecialchars($flash['message'] ?? '', ENT_QUOTES, 'UTF-8');
    
    $iconMap = [
        'success' => 'fa-check-circle',
        'error'   => 'fa-exclamation-circle',
        'warning' => 'fa-exclamation-triangle',
        'info'    => 'fa-info-circle'
    ];
    
    $icon = $iconMap[$type] ?? 'fa-info-circle';
    
    return <<<HTML
    <div class="alert alert-{$type} alert-flash" role="alert" data-autohide="5000">
        <div class="alert-inner">
            <i class="fas {$icon} alert-icon"></i>
            <span class="alert-message">{$message}</span>
            <button type="button" class="alert-close" data-dismiss="alert" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    HTML;
}
?>