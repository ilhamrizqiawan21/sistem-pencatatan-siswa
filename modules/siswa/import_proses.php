<?php
require_once '../../config/db.php';
require_once '../../config/auth.php';
requireLogin();

require_once '../../config/functions.php';

require_once '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_excel'])) {
    // Verify CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        session_start();
        $_SESSION['import_message'] = 'Request tidak valid (CSRF).';
        header('Location: index.php');
        exit;
    }
    $file = $_FILES['file_excel']['tmp_name'];
    
    try {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        
        // Hapus baris header (baris pertama)
        array_shift($rows);
        
        $success = 0;
        $errors = [];
        
        // Ambil mapping nama_kelas => id
        $kelas_map = [];
        $kelas_list = $pdo->query("SELECT id, nama_kelas FROM kelas")->fetchAll();
        foreach ($kelas_list as $k) {
            $kelas_map[strtolower(trim($k['nama_kelas']))] = $k['id'];
        }
        
        foreach ($rows as $rowIndex => $row) {
            // Lewati baris kosong
            if (empty(array_filter($row))) continue;
            
            $nis = trim($row[0] ?? '');
            $nama = trim($row[1] ?? '');
            $nama_kelas = trim($row[2] ?? '');
            $jenis_kelamin = strtoupper(trim($row[3] ?? ''));
            
            // Validasi
            if (empty($nis)) {
                $errors[] = "Baris " . ($rowIndex+2) . ": NIS kosong.";
                continue;
            }
            if (empty($nama)) {
                $errors[] = "Baris " . ($rowIndex+2) . ": Nama kosong.";
                continue;
            }
            if (empty($nama_kelas)) {
                $errors[] = "Baris " . ($rowIndex+2) . ": Kelas kosong.";
                continue;
            }
            if (!in_array($jenis_kelamin, ['L', 'P'])) {
                $errors[] = "Baris " . ($rowIndex+2) . ": Jenis kelamin harus L atau P.";
                continue;
            }
            
            // Cari atau buat kelas
            $kelas_id = $kelas_map[strtolower($nama_kelas)] ?? null;
            if (!$kelas_id) {
                $stmt = $pdo->prepare("INSERT INTO kelas (nama_kelas) VALUES (?)");
                $stmt->execute([$nama_kelas]);
                $kelas_id = $pdo->lastInsertId();
                $kelas_map[strtolower($nama_kelas)] = $kelas_id;
            }
            
            // Cek duplikat NIS
            $stmt = $pdo->prepare("SELECT id FROM siswa WHERE nis = ?");
            $stmt->execute([$nis]);
            if ($stmt->fetch()) {
                $errors[] = "Baris " . ($rowIndex+2) . ": NIS $nis sudah terdaftar.";
                continue;
            }
            
            // Insert siswa (hanya 4 field + status=1, lainnya null)
            $sql = "INSERT INTO siswa (nis, nama, kelas_id, jenis_kelamin, status) VALUES (?,?,?,?,1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nis, $nama, $kelas_id, $jenis_kelamin]);
            $success++;
        }
        
        // Set pesan session
        session_start();
        $_SESSION['import_message'] = "Import selesai. Berhasil: $success siswa.";
        if (!empty($errors)) {
            $_SESSION['import_errors'] = $errors;
        }
        
    } catch (Exception $e) {
        session_start();
        $_SESSION['import_message'] = "Terjadi kesalahan: " . $e->getMessage();
    }
    
    header('Location: index.php');
    exit;
} else {
    header('Location: index.php');
    exit;
}