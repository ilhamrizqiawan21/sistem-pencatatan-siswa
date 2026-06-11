<?php
require_once '../../config/db.php';
require_once '../../config/auth.php';
require_once '../../config/functions.php';
requireLogin();

// Session sudah aktif dari auth.php, tidak perlu start lagi
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // opsional sebagai pengaman
}

if (isset($_POST['simpan'])) {
    // CSRF check
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        header('Location: index.php?error=CSRF%20token%20tidak%20valid');
        exit;
    }
    $id = $_POST['id'] ?? 0;
    $nis = trim($_POST['nis']);
    $nama = trim($_POST['nama']);
    $kelas_id = (int)$_POST['kelas_id'];
    $jenis_kelamin = $_POST['jenis_kelamin']; // L / P

    // Validasi sederhana
    if (empty($nis) || empty($nama) || empty($kelas_id) || empty($jenis_kelamin)) {
        header('Location: index.php?error=Data tidak lengkap');
        exit;
    }

    if ($id > 0) {
        // Update
        $stmt = $pdo->prepare("UPDATE siswa SET nis=?, nama=?, kelas_id=?, jenis_kelamin=? WHERE id=?");
        $stmt->execute([$nis, $nama, $kelas_id, $jenis_kelamin, $id]);
    } else {
        // Insert, isi field lain dengan nilai default (null atau 1)
        $stmt = $pdo->prepare("INSERT INTO siswa (nis, nama, kelas_id, jenis_kelamin, status) VALUES (?,?,?,?,1)");
        $stmt->execute([$nis, $nama, $kelas_id, $jenis_kelamin]);
    }
} elseif (isset($_POST['action']) && $_POST['action'] == 'hapus') {
    // Deletion must be POST with valid CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        header('Location: index.php?error=CSRF%20token%20tidak%20valid');
        exit;
    }
    $id = (int)$_POST['id'];
    // Soft delete: set status=0
    $stmt = $pdo->prepare("UPDATE siswa SET status=0 WHERE id=?");
    $stmt->execute([$id]);
}
header('Location: index.php');
exit;