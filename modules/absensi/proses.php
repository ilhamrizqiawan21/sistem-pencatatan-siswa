<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../config/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tanggal'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        session_start();
        $_SESSION['error'] = 'Request tidak valid.';
        header('Location: index.php');
        exit;
    }
    $tanggal = $_POST['tanggal'];
    $ta_id = (int)$_POST['tahun_ajaran_id'];
    $kelas_id = (int)$_POST['kelas_id'];
    $siswa_ids = $_POST['siswa_id'];
    $statuses = $_POST['status'];
    $keterangans = $_POST['keterangan'];

    for ($i = 0; $i < count($siswa_ids); $i++) {
        $siswa_id = (int)$siswa_ids[$i];
        $status = $statuses[$i];
        $keterangan = trim($keterangans[$i]);

        // Cek apakah sudah ada data absensi untuk siswa dan tanggal ini
        $stmt = $pdo->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND tanggal = ?");
        $stmt->execute([$siswa_id, $tanggal]);
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE absensi SET status = ?, keterangan = ? WHERE siswa_id = ? AND tanggal = ?");
            $stmt->execute([$status, $keterangan, $siswa_id, $tanggal]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO absensi (siswa_id, tahun_ajaran_id, tanggal, status, keterangan) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$siswa_id, $ta_id, $tanggal, $status, $keterangan]);
        }
    }

    session_start();
    $_SESSION['success'] = "Absensi tanggal " . date('d-m-Y', strtotime($tanggal)) . " untuk kelas berhasil disimpan.";
    header("Location: index.php?kelas_id=$kelas_id&tanggal=$tanggal");
    exit;
} else {
    header("Location: index.php");
    exit;
}