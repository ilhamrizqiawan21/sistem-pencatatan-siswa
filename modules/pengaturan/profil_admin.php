<?php
$pageTitle = 'Edit Profil Admin';
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../config/auth.php';
require_once '../../includes/header.php';
requireLogin();

// Hanya role admin yang boleh
if ($_SESSION['role'] != 'admin') {

    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$error = '';
$success = '';

// Ambil data admin (user_id dari session)
$stmt = $pdo->prepare("SELECT id, nama, username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();

if (!$admin) {
    die('Admin tidak ditemukan.');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Request tidak valid.';
    } else {
        $nama = trim($_POST['nama']);
        $username = trim($_POST['username']);
        $password_lama = $_POST['password_lama'];
        $password_baru = $_POST['password_baru'];
        $konfirmasi = $_POST['konfirmasi_password'];

        if (empty($nama) || empty($username)) {
            $error = 'Nama dan username tidak boleh kosong.';
        } else {
            // Cek username duplikat (kecuali dirinya sendiri)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $admin['id']]);
            if ($stmt->fetch()) {
                $error = 'Username sudah digunakan oleh user lain.';
            } elseif (!empty($password_baru)) {
                // Ada perubahan password
                if (empty($password_lama)) {
                    $error = 'Password lama harus diisi untuk mengubah password.';
                } else {
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$admin['id']]);
                    $hash = $stmt->fetchColumn();
                    if (!password_verify($password_lama, $hash)) {
                        $error = 'Password lama salah.';
                    } elseif (strlen($password_baru) < 4) {
                        $error = 'Password baru minimal 4 karakter.';
                    } elseif ($password_baru !== $konfirmasi) {
                        $error = 'Konfirmasi password baru tidak cocok.';
                    } else {
                        $hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET nama = ?, username = ?, password = ? WHERE id = ?");
                        $stmt->execute([$nama, $username, $hash_baru, $admin['id']]);
                        $success = 'Profil berhasil diperbarui.';
                        // Update session
                        $_SESSION['nama'] = $nama;
                        // Refresh data admin
                        $admin['nama'] = $nama;
                        $admin['username'] = $username;
                    }
                }
            } else {
                // Hanya update nama dan username
                $stmt = $pdo->prepare("UPDATE users SET nama = ?, username = ? WHERE id = ?");
                $stmt->execute([$nama, $username, $admin['id']]);
                $success = 'Profil berhasil diperbarui.';
                $_SESSION['nama'] = $nama;
                $admin['nama'] = $nama;
                $admin['username'] = $username;
            }
        }
    }
}
?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-user-edit"></i> Edit Profil Admin
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($admin['nama']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($admin['username']) ?>" required>
            </div>
            <hr>
            <h6>Ganti Password (opsional)</h6>
            <div class="mb-3">
                <label class="form-label">Password Lama</label>
                <input type="password" name="password_lama" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Password Baru</label>
                <input type="password" name="password_baru" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="konfirmasi_password" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>