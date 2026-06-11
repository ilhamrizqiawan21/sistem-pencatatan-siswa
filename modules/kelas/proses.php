<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../config/auth.php';
requireLogin();

if(isset($_POST['simpan'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        setFlash('Request tidak valid', 'error');
        header('Location: index.php');
        exit;
    }
    $id = $_POST['id'] ?? 0;
    $nama = $_POST['nama_kelas'];
    $wali = $_POST['wali_kelas'];
    if($id > 0) {
        $stmt = $pdo->prepare("UPDATE kelas SET nama_kelas=?, wali_kelas=? WHERE id=?");
        $stmt->execute([$nama, $wali, $id]);
        setFlash('Data kelas berhasil diperbarui', 'success');
    } else {
        $stmt = $pdo->prepare("INSERT INTO kelas (nama_kelas, wali_kelas) VALUES (?,?)");
        $stmt->execute([$nama, $wali]);
        setFlash('Data kelas berhasil ditambahkan', 'success');
    }
}
elseif(isset($_GET['action']) && $_GET['action'] == 'hapus') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM kelas WHERE id=?");
    $stmt->execute([$id]);
    setFlash('Data kelas berhasil dihapus', 'success');
}
header('Location: index.php');
exit;