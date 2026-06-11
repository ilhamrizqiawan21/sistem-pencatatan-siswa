<?php
require_once '../../vendor/autoload.php';
require_once '../../config/db.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$mod = $_GET['mod'] ?? '';
$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$siswa_id = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;

// Rentang tanggal (opsional)
$tahun_mulai = isset($_GET['tahun_mulai']) ? (int)$_GET['tahun_mulai'] : 0;
$bulan_mulai = isset($_GET['bulan_mulai']) ? (int)$_GET['bulan_mulai'] : 0;
$tahun_selesai = isset($_GET['tahun_selesai']) ? (int)$_GET['tahun_selesai'] : 0;
$bulan_selesai = isset($_GET['bulan_selesai']) ? (int)$_GET['bulan_selesai'] : 0;

// Bangun kondisi tanggal (jika rentang valid)
$tanggal_where = '';
$tanggal_params = [];
if ($tahun_mulai && $bulan_mulai && $tahun_selesai && $bulan_selesai) {
    $start_date = sprintf("%04d-%02d-01", $tahun_mulai, $bulan_mulai);
    $end_date = date("Y-m-t", strtotime(sprintf("%04d-%02d-01", $tahun_selesai, $bulan_selesai)));
    $tanggal_where = " AND tanggal BETWEEN ? AND ? ";
    $tanggal_params = [$start_date, $end_date];
} elseif ($tahun_mulai && $bulan_mulai) {
    $start_date = sprintf("%04d-%02d-01", $tahun_mulai, $bulan_mulai);
    $tanggal_where = " AND tanggal >= ? ";
    $tanggal_params = [$start_date];
} elseif ($tahun_selesai && $bulan_selesai) {
    $end_date = date("Y-m-t", strtotime(sprintf("%04d-%02d-01", $tahun_selesai, $bulan_selesai)));
    $tanggal_where = " AND tanggal <= ? ";
    $tanggal_params = [$end_date];
}

if (!$mod) die('Pilih jenis data terlebih dahulu.');

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle(ucfirst($mod));

$headerStyle = [
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF3B82F6']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
];

function setHeaders($sheet, $headers, $row = 1) {
    $col = 'A';
    foreach ($headers as $h) {
        $sheet->setCellValue($col.$row, $h);
        $sheet->getStyle($col.$row)->applyFromArray($GLOBALS['headerStyle']);
        $col++;
    }
}

// Helper untuk menambahkan where kelas & siswa
function getFilterWhere($kelas_id, $siswa_id, array &$params) {
    $where = [];
    if ($kelas_id > 0) {
        $where[] = "s.kelas_id = ?";
        $params[] = $kelas_id;
    }
    if ($siswa_id > 0) {
        $where[] = "s.id = ?";
        $params[] = $siswa_id;
    }
    return empty($where) ? '' : ' AND ' . implode(' AND ', $where);
}

// === PELANGGARAN ===
if ($mod == 'pelanggaran') {
    $params = $tanggal_params;
    $sql = "SELECT s.nis, s.nama, k.nama_kelas, jp.nama as pelanggaran, jp.poin, p.tanggal, p.keterangan
            FROM pelanggaran p
            JOIN siswa s ON p.siswa_id = s.id
            JOIN kelas k ON s.kelas_id = k.id
            JOIN jenis_pelanggaran jp ON p.jenis_pelanggaran_id = jp.id
            WHERE 1=1 $tanggal_where";
    $sql .= getFilterWhere($kelas_id, $siswa_id, $params);
    $sql .= " ORDER BY p.tanggal DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    setHeaders($sheet, ['NIS', 'Nama', 'Kelas', 'Jenis Pelanggaran', 'Poin', 'Tanggal', 'Keterangan']);
    $row = 2;
    foreach ($data as $d) {
        $sheet->setCellValue('A'.$row, $d['nis']);
        $sheet->setCellValue('B'.$row, $d['nama']);
        $sheet->setCellValue('C'.$row, $d['nama_kelas']);
        $sheet->setCellValue('D'.$row, $d['pelanggaran']);
        $sheet->setCellValue('E'.$row, $d['poin']);
        $sheet->setCellValue('F'.$row, $d['tanggal']);
        $sheet->setCellValue('G'.$row, $d['keterangan']);
        $row++;
    }
}
// === KETERLAMBATAN ===
elseif ($mod == 'keterlambatan') {
    $params = $tanggal_params;
    $sql = "SELECT s.nis, s.nama, k.nama_kelas, kt.tanggal, kt.jam_datang, kt.alasan
            FROM keterlambatan kt
            JOIN siswa s ON kt.siswa_id = s.id
            JOIN kelas k ON s.kelas_id = k.id
            WHERE 1=1 $tanggal_where";
    $sql .= getFilterWhere($kelas_id, $siswa_id, $params);
    $sql .= " ORDER BY kt.tanggal DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    setHeaders($sheet, ['NIS', 'Nama', 'Kelas', 'Tanggal', 'Jam Datang', 'Alasan']);
    $row = 2;
    foreach ($data as $d) {
        $sheet->setCellValue('A'.$row, $d['nis']);
        $sheet->setCellValue('B'.$row, $d['nama']);
        $sheet->setCellValue('C'.$row, $d['nama_kelas']);
        $sheet->setCellValue('D'.$row, $d['tanggal']);
        $sheet->setCellValue('E'.$row, $d['jam_datang']);
        $sheet->setCellValue('F'.$row, $d['alasan']);
        $row++;
    }
}
// === PRESTASI ===
elseif ($mod == 'prestasi') {
    $params = $tanggal_params;
    $sql = "SELECT s.nis, s.nama, k.nama_kelas, p.nama_prestasi, tp.nama as tingkat, p.juara, p.tanggal, p.penyelenggara
            FROM prestasi p
            JOIN siswa s ON p.siswa_id = s.id
            JOIN kelas k ON s.kelas_id = k.id
            JOIN tingkat_prestasi tp ON p.tingkat_prestasi_id = tp.id
            WHERE 1=1 $tanggal_where";
    $sql .= getFilterWhere($kelas_id, $siswa_id, $params);
    $sql .= " ORDER BY p.tanggal DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    setHeaders($sheet, ['NIS', 'Nama', 'Kelas', 'Nama Prestasi', 'Tingkat', 'Juara', 'Tanggal', 'Penyelenggara']);
    $row = 2;
    foreach ($data as $d) {
        $sheet->setCellValue('A'.$row, $d['nis']);
        $sheet->setCellValue('B'.$row, $d['nama']);
        $sheet->setCellValue('C'.$row, $d['nama_kelas']);
        $sheet->setCellValue('D'.$row, $d['nama_prestasi']);
        $sheet->setCellValue('E'.$row, $d['tingkat']);
        $sheet->setCellValue('F'.$row, $d['juara']);
        $sheet->setCellValue('G'.$row, $d['tanggal']);
        $sheet->setCellValue('H'.$row, $d['penyelenggara']);
        $row++;
    }
}
// === REKAP ABSENSI ===
elseif ($mod == 'absensi') {
    // Untuk absensi, kita perlu menghitung jumlah status dalam rentang tanggal
    $params = $tanggal_params;
    $where = "";
    if ($kelas_id > 0) {
        $where .= " AND s.kelas_id = ?";
        $params[] = $kelas_id;
    }
    if ($siswa_id > 0) {
        $where .= " AND s.id = ?";
        $params[] = $siswa_id;
    }
    if ($tanggal_where) {
        // ubah $tanggal_where yang sudah berisi "AND tanggal BETWEEN ..." menjadi untuk kondisi join
        $tgl_condition = str_replace("AND tanggal", "AND a.tanggal", $tanggal_where);
    } else {
        $tgl_condition = "";
    }
    $sql = "SELECT s.nis, s.nama, k.nama_kelas,
            SUM(CASE WHEN a.status='H' THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN a.status='I' THEN 1 ELSE 0 END) as izin,
            SUM(CASE WHEN a.status='S' THEN 1 ELSE 0 END) as sakit,
            SUM(CASE WHEN a.status='A' THEN 1 ELSE 0 END) as alpha
            FROM siswa s
            JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN absensi a ON s.id = a.siswa_id $tgl_condition
            WHERE s.status=1 $where
            GROUP BY s.id ORDER BY k.nama_kelas, s.nama";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    setHeaders($sheet, ['NIS', 'Nama', 'Kelas', 'Hadir', 'Izin', 'Sakit', 'Alpha']);
    $row = 2;
    foreach ($data as $d) {
        $sheet->setCellValue('A'.$row, $d['nis']);
        $sheet->setCellValue('B'.$row, $d['nama']);
        $sheet->setCellValue('C'.$row, $d['nama_kelas']);
        $sheet->setCellValue('D'.$row, $d['hadir']);
        $sheet->setCellValue('E'.$row, $d['izin']);
        $sheet->setCellValue('F'.$row, $d['sakit']);
        $sheet->setCellValue('G'.$row, $d['alpha']);
        $row++;
    }
}
// === DATA SISWA ===
elseif ($mod == 'siswa') {
    $params = [];
    $sql = "SELECT s.nis, s.nama, k.nama_kelas, s.jenis_kelamin
            FROM siswa s
            JOIN kelas k ON s.kelas_id = k.id
            WHERE s.status = 1";
    if ($kelas_id > 0) {
        $sql .= " AND s.kelas_id = ?";
        $params[] = $kelas_id;
    }
    if ($siswa_id > 0) {
        $sql .= " AND s.id = ?";
        $params[] = $siswa_id;
    }
    $sql .= " ORDER BY k.nama_kelas, s.nama";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    setHeaders($sheet, ['NIS', 'Nama', 'Kelas', 'Jenis Kelamin']);
    $row = 2;
    foreach ($data as $d) {
        $sheet->setCellValue('A'.$row, $d['nis']);
        $sheet->setCellValue('B'.$row, $d['nama']);
        $sheet->setCellValue('C'.$row, $d['nama_kelas']);
        $sheet->setCellValue('D'.$row, $d['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan');
        $row++;
    }
}
// === DATA KELAS ===
elseif ($mod == 'kelas') {
    $stmt = $pdo->prepare("SELECT id, nama_kelas, wali_kelas, created_at FROM kelas ORDER BY nama_kelas");
    $stmt->execute();
    $data = $stmt->fetchAll();
    setHeaders($sheet, ['ID', 'Nama Kelas', 'Wali Kelas', 'Tanggal Dibuat']);
    $row = 2;
    foreach ($data as $d) {
        $sheet->setCellValue('A'.$row, $d['id']);
        $sheet->setCellValue('B'.$row, $d['nama_kelas']);
        $sheet->setCellValue('C'.$row, $d['wali_kelas']);
        $sheet->setCellValue('D'.$row, date('d-m-Y', strtotime($d['created_at'])));
        $row++;
    }
} else {
    die('Modul tidak valid');
}

foreach (range('A', $sheet->getHighestColumn()) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$writer = new Xlsx($spreadsheet);
$filename = 'laporan_' . $mod . '_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$writer->save('php://output');
exit;