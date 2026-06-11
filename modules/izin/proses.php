<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../config/auth.php';
requireLogin();

function getSiswaId($pdo, $search_text) {
    $search = trim($search_text);
    if (empty($search)) return 0;
    $stmt = $pdo->prepare("SELECT id FROM siswa WHERE nis = ? OR nama = ? OR CONCAT(nis, ' - ', nama) = ?");
    $stmt->execute([$search, $search, $search]);
    $siswa = $stmt->fetch();
    return $siswa ? $siswa['id'] : 0;
}

if (isset($_POST['simpan'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        session_start();
        $_SESSION['error'] = 'Request tidak valid.';
        header('Location: index.php');
        exit;
    }
    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $siswa_search = trim($_POST['siswa_search'] ?? '');
    $ta_id = (int)$_POST['tahun_ajaran_id'];
    $tanggal = $_POST['tanggal'];
    $jenis = $_POST['jenis_izin'];
    $keterangan = trim($_POST['keterangan'] ?? '');

    if ($siswa_id == 0 && !empty($siswa_search)) {
        $siswa_id = getSiswaId($pdo, $siswa_search);
    }
    if ($siswa_id == 0) {
        session_start();
        $_SESSION['error'] = "Siswa tidak ditemukan: " . htmlspecialchars($siswa_search);
        header('Location: index.php');
        exit;
    }

    if ($jenis == 'pulang') {
        $jam = $_POST['jam_berangkat'];
        $alasan_pulang = $_POST['alasan_pulang'];
        $stmt = $pdo->prepare("INSERT INTO surat_izin (siswa_id, tahun_ajaran_id, jenis_izin, tanggal, jam_berangkat, alasan_pulang, keterangan) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$siswa_id, $ta_id, $jenis, $tanggal, $jam, $alasan_pulang, $keterangan]);
    } else {
        $alasan_biasa = trim($_POST['alasan_biasa']);
        $stmt = $pdo->prepare("INSERT INTO surat_izin (siswa_id, tahun_ajaran_id, jenis_izin, tanggal, alasan_biasa, keterangan) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$siswa_id, $ta_id, $jenis, $tanggal, $alasan_biasa, $keterangan]);
    }
    session_start();
    $_SESSION['success'] = "Data surat izin berhasil ditambahkan.";
    header("Location: index.php");
    exit;
} elseif (isset($_POST['edit'])) {
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
    $jenis = $_POST['jenis_izin'];
    $keterangan = trim($_POST['keterangan'] ?? '');

    if ($siswa_id == 0 && !empty($siswa_search)) {
        $siswa_id = getSiswaId($pdo, $siswa_search);
    }
    if ($siswa_id == 0) {
        session_start();
        $_SESSION['error'] = "Siswa tidak ditemukan: " . htmlspecialchars($siswa_search);
        header('Location: index.php');
        exit;
    }

    if ($jenis == 'pulang') {
        $jam = $_POST['jam_berangkat'];
        $alasan_pulang = $_POST['alasan_pulang'];
        $stmt = $pdo->prepare("UPDATE surat_izin SET siswa_id=?, tanggal=?, jenis_izin=?, jam_berangkat=?, alasan_pulang=?, keterangan=? WHERE id=?");
        $stmt->execute([$siswa_id, $tanggal, $jenis, $jam, $alasan_pulang, $keterangan, $id]);
    } else {
        $alasan_biasa = trim($_POST['alasan_biasa']);
        $stmt = $pdo->prepare("UPDATE surat_izin SET siswa_id=?, tanggal=?, jenis_izin=?, alasan_biasa=?, keterangan=? WHERE id=?");
        $stmt->execute([$siswa_id, $tanggal, $jenis, $alasan_biasa, $keterangan, $id]);
    }
    session_start();
    $_SESSION['success'] = "Data surat izin berhasil diperbarui.";
    header("Location: index.php");
    exit;
} elseif (isset($_GET['action']) && $_GET['action'] == 'hapus') {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM surat_izin WHERE id=?");
    $stmt->execute([$id]);
    session_start();
    $_SESSION['success'] = "Data surat izin berhasil dihapus.";
    header("Location: index.php");
    exit;
} else {
    header("Location: index.php");
    exit;
}