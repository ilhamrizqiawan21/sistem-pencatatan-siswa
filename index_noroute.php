<?php
// TEMP VERSION - No routing, direct access only
// Access: https://ilham.didzacorp.com/mts-alihsan/index_noroute.php

// Load environment
require_once 'config/env.php';
require_once 'config/error-handler.php';
require_once 'config/constants.php';
require_once 'config/db.php';
require_once 'config/auth.php';
require_once 'config/functions.php';

// Check login
if (!isLoggedIn()) {
    // Show login form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Login - MTs Al-Ihsan</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }</style>
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card shadow-lg">
                        <div class="card-header bg-primary text-white" style="padding: 30px;">
                            <h3 class="mb-0">MTs Al-Ihsan</h3>
                            <small>Sistem Pencatatan</small>
                        </div>
                        <div class="card-body" style="padding: 30px;">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control" required autofocus>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Login</button>
                            </form>
                            <div class="mt-3 text-muted small">
                                <strong>Demo:</strong> admin / password
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// User is logged in - show dashboard
$pageTitle = 'Dashboard';
require_once 'includes/header.php';

// Get data
$total_siswa = $pdo->query("SELECT COUNT(*) FROM siswa WHERE status = 1")->fetchColumn();
$total_kelas = $pdo->query("SELECT COUNT(*) FROM kelas")->fetchColumn();

?>
<div class="container-fluid mt-4">
    <div class="alert alert-info">
        <strong>⚠️ Testing Mode:</strong> This is index_noroute.php (no URL routing)
        <a href="index_noroute.php?logout=1" class="btn btn-sm btn-secondary float-end">Logout</a>
    </div>
    
    <h1>Dashboard</h1>
    <p>Total Siswa: <?php echo $total_siswa; ?></p>
    <p>Total Kelas: <?php echo $total_kelas; ?></p>
    
    <hr>
    <p><a href="simple_test.php">→ Back to Simple Test</a></p>
</div>

<?php
if (isset($_GET['logout'])) {
    logout();
    header('Location: index_noroute.php');
    exit;
}

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        login($user['id'], $user['role'], $user['nama']);
        header('Location: index_noroute.php');
        exit;
    } else {
        echo '<div class="alert alert-danger mt-3">Login failed</div>';
    }
}
?>
