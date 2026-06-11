<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../config/auth.php';
requireLogin();

if (isset($_POST['simpan'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Request tidak valid.';
        header('Location: index.php');
        exit;
    }
    $tahun_ajaran_id = (int)$_POST['tahun_ajaran_id'];
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'] ?? '';
    $jenis_input = $_POST['jenis_pelanggaran_id'];
    
    // Ambil siswa_id dari hidden atau cari dari siswa_search
    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $siswa_search = trim($_POST['siswa_search'] ?? '');
    
    if ($siswa_id == 0 && !empty($siswa_search)) {
        // Cari berdasarkan teks input (nis, nama, atau kombinasi "nis - nama")
        $stmt = $pdo->prepare("SELECT id FROM siswa WHERE nis = ? OR nama = ? OR CONCAT(nis, ' - ', nama) = ?");
        $stmt->execute([$siswa_search, $siswa_search, $siswa_search]);
        $siswa = $stmt->fetch();
        if ($siswa) {
            $siswa_id = $siswa['id'];
        } else {
            $_SESSION['error'] = "Siswa tidak ditemukan: " . htmlspecialchars($siswa_search);
            header('Location: index.php');
            exit;
        }
    }
    
    if ($siswa_id == 0) {
        $_SESSION['error'] = "Pilih siswa terlebih dahulu.";
        header('Location: index.php');
        exit;
    }
    
    // Proses jenis pelanggaran
    if ($jenis_input === 'lainnya') {
        $nama_baru = trim($_POST['nama_pelanggaran_baru']);
        $poin_baru = (int)($_POST['poin_baru'] ?? 5);
        if (empty($nama_baru)) {
            $_SESSION['error'] = "Nama pelanggaran baru harus diisi.";
            header('Location: index.php');
            exit;
        }
        // Cek duplikat (case-insensitive)
        $stmt = $pdo->prepare("SELECT id FROM jenis_pelanggaran WHERE LOWER(nama) = LOWER(?)");
        $stmt->execute([$nama_baru]);
        $existing = $stmt->fetch();
        if ($existing) {
            $jenis_pelanggaran_id = $existing['id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO jenis_pelanggaran (nama, poin) VALUES (?, ?)");
            $stmt->execute([$nama_baru, $poin_baru]);
            $jenis_pelanggaran_id = $pdo->lastInsertId();
        }
    } else {
        $jenis_pelanggaran_id = (int)$jenis_input;
    }
    
    // Insert pelanggaran
    $stmt = $pdo->prepare("INSERT INTO pelanggaran (siswa_id, tahun_ajaran_id, jenis_pelanggaran_id, tanggal, keterangan) VALUES (?,?,?,?,?)");
    $stmt->execute([$siswa_id, $tahun_ajaran_id, $jenis_pelanggaran_id, $tanggal, $keterangan]);
    
    header('Location: index.php');
    exit;
} elseif (isset($_GET['action']) && $_GET['action'] == 'hapus') {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM pelanggaran WHERE id=?");
    $stmt->execute([$id]);
    header('Location: index.php');
    exit;
} else {
    header('Location: index.php');
    exit;
}