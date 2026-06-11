<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../config/auth.php';
requireLogin();

// Fungsi untuk mendapatkan siswa_id dari teks input (nis, nama, atau "nis - nama")
function getSiswaId($pdo, $search_text) {
    $search = trim($search_text);
    if (empty($search)) return 0;
    $stmt = $pdo->prepare("SELECT id FROM siswa WHERE nis = ? OR nama = ? OR CONCAT(nis, ' - ', nama) = ?");
    $stmt->execute([$search, $search, $search]);
    $siswa = $stmt->fetch();
    return $siswa ? $siswa['id'] : 0;
}

// Simpan baru
if (isset($_POST['simpan'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        session_start();
        $_SESSION['error'] = 'Request tidak valid.';
        header('Location: index.php');
        exit;
    }
    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $siswa_search = trim($_POST['siswa_search'] ?? '');
    $tahun_ajaran_id = (int)$_POST['tahun_ajaran_id'];
    $tanggal = $_POST['tanggal'];
    $jam_datang = $_POST['jam_datang'];
    $alasan = trim($_POST['alasan']);

    if ($siswa_id == 0 && !empty($siswa_search)) {
        $siswa_id = getSiswaId($pdo, $siswa_search);
    }
    if ($siswa_id == 0) {
        session_start();
        $_SESSION['error'] = "Siswa tidak ditemukan: " . htmlspecialchars($siswa_search);
        header('Location: index.php');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO keterlambatan (siswa_id, tahun_ajaran_id, tanggal, jam_datang, alasan) VALUES (?,?,?,?,?)");
    $stmt->execute([$siswa_id, $tahun_ajaran_id, $tanggal, $jam_datang, $alasan]);
    session_start();
    $_SESSION['success'] = "Data keterlambatan berhasil ditambahkan.";
    header("Location: index.php");
    exit;
}
// Edit
elseif (isset($_POST['edit'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        session_start();
        $_SESSION['error'] = 'Request tidak valid.';
        header('Location: index.php');
        exit;
    }
    $id = (int)$_POST['id'];
    $siswa_id = (int)($_POST['siswa_id_edit'] ?? 0);
    $siswa_search = trim($_POST['siswa_search_edit'] ?? '');
    $tanggal = $_POST['tanggal'];
    $jam_datang = $_POST['jam_datang'];
    $alasan = trim($_POST['alasan']);

    if ($siswa_id == 0 && !empty($siswa_search)) {
        $siswa_id = getSiswaId($pdo, $siswa_search);
    }
    if ($siswa_id == 0) {
        session_start();
        $_SESSION['error'] = "Siswa tidak ditemukan: " . htmlspecialchars($siswa_search);
        header('Location: index.php');
        exit;
    }

    $stmt = $pdo->prepare("UPDATE keterlambatan SET siswa_id=?, tanggal=?, jam_datang=?, alasan=? WHERE id=?");
    $stmt->execute([$siswa_id, $tanggal, $jam_datang, $alasan, $id]);
    session_start();
    $_SESSION['success'] = "Data keterlambatan berhasil diperbarui.";
    header("Location: index.php");
    exit;
}
// Hapus
elseif (isset($_GET['action']) && $_GET['action'] == 'hapus') {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM keterlambatan WHERE id=?");
    $stmt->execute([$id]);
    session_start();
    $_SESSION['success'] = "Data keterlambatan berhasil dihapus.";
    header("Location: index.php");
    exit;
} else {
    header("Location: index.php");
    exit;
}