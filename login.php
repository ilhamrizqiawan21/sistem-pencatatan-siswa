<?php
// Load environment variables FIRST
require_once 'config/env.php';

// Load error handler SECOND
require_once 'config/error-handler.php';

// Load configuration
require_once 'config/constants.php';
require_once 'config/db.php';
require_once 'config/auth.php';
require_once 'config/functions.php';

if (isLoggedIn()) {
    redirectTo('dashboard');
}

$error = '';
// Simple brute-force protection
$maxAttempts = 5;
$lockoutSeconds = 300; // 5 minutes
if (!isset($_SESSION['failed_login'])) {
    $_SESSION['failed_login'] = 0;
}
if (!isset($_SESSION['last_failed_time'])) {
    $_SESSION['last_failed_time'] = 0;
}

// Check lockout
if ($_SESSION['failed_login'] >= $maxAttempts && (time() - $_SESSION['last_failed_time']) < $lockoutSeconds) {
    $remaining = $lockoutSeconds - (time() - $_SESSION['last_failed_time']);
    $error = 'Terlalu banyak percobaan. Coba lagi setelah ' . ceil($remaining/60) . ' menit.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['failed_login'] = ($_SESSION['failed_login'] ?? 0) + 1;
        $_SESSION['last_failed_time'] = time();
        $error = 'Request tidak valid.';
    } else {
    $username = isset($_POST['username']) ? substr(trim($_POST['username']), 0, 100) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // basic sanitization
    $username = filter_var($username, FILTER_SANITIZE_STRING);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // reset counters
        $_SESSION['failed_login'] = 0;
        $_SESSION['last_failed_time'] = 0;
        login($user['id'], $user['role'], $user['nama']);
        redirectTo('dashboard');
    } else {
        $_SESSION['failed_login']++;
        $_SESSION['last_failed_time'] = time();
        $error = 'Username atau password salah.';
    }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - MTs Al-Ihsan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 400px;">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">Login Sistem Pencatatan</div>
        <div class="card-body">
            <?php if($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <div class="mt-3 text-muted small">* default admin: admin / password</div>
        </div>
    </div>
</div>
</body>
</html>