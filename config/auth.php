<?php
session_name('MTS_ALIHSAN_SESSION');
// Secure session cookie params (set before session_start)
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
if ($secure) {
    ini_set('session.cookie_secure', 1);
}
// set SameSite if supported (PHP 7.3+ handled by session.cookie_samesite)
if (PHP_VERSION_ID >= 70300) {
    ini_set('session.cookie_samesite', 'Lax');
}
session_start();
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/constants.php';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function login($user_id, $role, $nama) {
    // Regenerate session id to prevent session fixation
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user_id;
    $_SESSION['role'] = $role;
    $_SESSION['nama'] = $nama;
    $_SESSION['last_activity'] = time();
}

function logout() {
    // Unset session and regenerate id
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    session_regenerate_id(true);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}
?>