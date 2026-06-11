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
    $nama_prestasi = trim($_POST['nama_prestasi']);
    $tingkat = (int)$_POST['tingkat_prestasi_id'];
    $juara = trim($_POST['juara']);
    $tanggal = $_POST['tanggal'];
    $penyelenggara = trim($_POST['penyelenggara']);
    $keterangan = trim($_POST['keterangan']);

    if ($siswa_id == 0 && !empty($siswa_search)) {
        $siswa_id = getSiswaId($pdo, $siswa_search);
    }
    if ($siswa_id == 0) {
        session_start();
        $_SESSION['error'] = "Siswa tidak ditemukan: " . htmlspecialchars($siswa_search);
        header('Location: index.php');
        exit;
    }

    $foto = null;
    if (!empty($_FILES['foto']['name'])) {
        $targetDir = UPLOAD_PATH . 'prestasi/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $upload = uploadFile($_FILES['foto'], $targetDir);
        if (isset($upload['success'])) $foto = $upload['filename'];
        else {
            session_start();
            $_SESSION['error'] = $upload['error'];
            header('Location: index.php');
            exit;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO prestasi (siswa_id, tahun_ajaran_id, nama_prestasi, tingkat_prestasi_id, juara, tanggal, penyelenggara, foto, keterangan) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$siswa_id, $ta_id, $nama_prestasi, $tingkat, $juara, $tanggal, $penyelenggara, $foto, $keterangan]);
    session_start();
    $_SESSION['success'] = "Prestasi berhasil ditambahkan.";
    header('Location: index.php');
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
    $nama_prestasi = trim($_POST['nama_prestasi']);
    $tingkat = (int)$_POST['tingkat_prestasi_id'];
    $juara = trim($_POST['juara']);
    $tanggal = $_POST['tanggal'];
    $penyelenggara = trim($_POST['penyelenggara']);
    $keterangan = trim($_POST['keterangan']);

    if ($siswa_id == 0 && !empty($siswa_search)) {
        $siswa_id = getSiswaId($pdo, $siswa_search);
    }
    if ($siswa_id == 0) {
        session_start();
        $_SESSION['error'] = "Siswa tidak ditemukan: " . htmlspecialchars($siswa_search);
        header('Location: index.php');
        exit;
    }

    // Update foto jika ada
    $foto = null;
    if (!empty($_FILES['foto']['name'])) {
        $targetDir = UPLOAD_PATH . 'prestasi/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $upload = uploadFile($_FILES['foto'], $targetDir);
        if (isset($upload['success'])) $foto = $upload['filename'];
        else {
            session_start();
            $_SESSION['error'] = $upload['error'];
            header('Location: index.php');
            exit;
        }
    }

    if ($foto) {
        $stmt = $pdo->prepare("UPDATE prestasi SET siswa_id=?, nama_prestasi=?, tingkat_prestasi_id=?, juara=?, tanggal=?, penyelenggara=?, foto=?, keterangan=? WHERE id=?");
        $stmt->execute([$siswa_id, $nama_prestasi, $tingkat, $juara, $tanggal, $penyelenggara, $foto, $keterangan, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE prestasi SET siswa_id=?, nama_prestasi=?, tingkat_prestasi_id=?, juara=?, tanggal=?, penyelenggara=?, keterangan=? WHERE id=?");
        $stmt->execute([$siswa_id, $nama_prestasi, $tingkat, $juara, $tanggal, $penyelenggara, $keterangan, $id]);
    }
    session_start();
    $_SESSION['success'] = "Prestasi berhasil diperbarui.";
    header('Location: index.php');
    exit;
} elseif (isset($_GET['action']) && $_GET['action'] == 'hapus') {
    $id = (int)$_GET['id'];
    // Opsional hapus file foto
    $stmt = $pdo->prepare("SELECT foto FROM prestasi WHERE id=?");
    $stmt->execute([$id]);
    $foto = $stmt->fetchColumn();
    if ($foto && file_exists(UPLOAD_PATH . 'prestasi/' . $foto)) {
        unlink(UPLOAD_PATH . 'prestasi/' . $foto);
    }
    $stmt = $pdo->prepare("DELETE FROM prestasi WHERE id=?");
    $stmt->execute([$id]);
    session_start();
    $_SESSION['success'] = "Prestasi berhasil dihapus.";
    header('Location: index.php');
    exit;
} else {
    header('Location: index.php');
    exit;
}