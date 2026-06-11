<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../config/auth.php';
requireLogin();

if(isset($_POST['simpan'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        header('Location: index.php?error=Request%20tidak%20valid');
        exit;
    }
    $tahun = $_POST['tahun'];
    $semester = $_POST['semester'];
    $stmt = $pdo->prepare("INSERT INTO tahun_ajaran (tahun, semester) VALUES (?,?)");
    $stmt->execute([$tahun, $semester]);
}
header('Location: index.php');
exit;