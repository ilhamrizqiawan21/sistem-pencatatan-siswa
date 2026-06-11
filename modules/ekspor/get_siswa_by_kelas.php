<?php
require_once '../../config/db.php';

header('Content-Type: application/json');

$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$siswa_terpilih = isset($_GET['siswa_terpilih']) ? (int)$_GET['siswa_terpilih'] : 0;

$siswa = [];

if ($kelas_id > 0) {
    // Ambil hanya siswa dari kelas yang dipilih
    $stmt = $pdo->prepare("SELECT id, nis, nama FROM siswa WHERE kelas_id = ? AND status = 1 ORDER BY nama");
    $stmt->execute([$kelas_id]);
    $siswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Jika kelas = 0, ambil semua siswa (opsional)
    $stmt = $pdo->prepare("SELECT id, nis, nama FROM siswa WHERE status = 1 ORDER BY nama");
    $stmt->execute();
    $siswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($siswa);
?>