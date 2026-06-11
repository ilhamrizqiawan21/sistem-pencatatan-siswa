<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../config/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        header('Location: index.php?error=Request%20tidak%20valid');
        exit;
    }
    $kelas_id = (int)$_POST['kelas_id'];
    $ta_id = (int)$_POST['tahun_ajaran_id'];
    $tanggal = $_POST['tanggal'];
    $nilai_lantai = (int)$_POST['nilai_lantai'];
    $nilai_sampah = (int)$_POST['nilai_sampah'];
    $nilai_rak = (int)$_POST['nilai_rak'];
    $nilai_penataan = (int)$_POST['nilai_penataan'];
    $keterangan = trim($_POST['keterangan'] ?? '');

    $cek = $pdo->prepare("SELECT id FROM kebersihan_kelas WHERE kelas_id = ? AND tanggal = ? AND tahun_ajaran_id = ?");
    $cek->execute([$kelas_id, $tanggal, $ta_id]);
    if ($cek->fetch()) {
        $stmt = $pdo->prepare("UPDATE kebersihan_kelas SET nilai_lantai=?, nilai_sampah=?, nilai_rak=?, nilai_penataan=?, keterangan=? WHERE kelas_id=? AND tanggal=? AND tahun_ajaran_id=?");
        $stmt->execute([$nilai_lantai, $nilai_sampah, $nilai_rak, $nilai_penataan, $keterangan, $kelas_id, $tanggal, $ta_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO kebersihan_kelas (kelas_id, tahun_ajaran_id, tanggal, nilai_lantai, nilai_sampah, nilai_rak, nilai_penataan, keterangan) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$kelas_id, $ta_id, $tanggal, $nilai_lantai, $nilai_sampah, $nilai_rak, $nilai_penataan, $keterangan]);
    }
}
header("Location: index.php");
exit;