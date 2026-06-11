<?php
require_once '../../vendor/autoload.php';
require_once '../../config/db.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

$sql = "SELECT s.nis, s.nama, k.nama_kelas,
        SUM(CASE WHEN a.status = 'H' THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN a.status = 'I' THEN 1 ELSE 0 END) as izin,
        SUM(CASE WHEN a.status = 'S' THEN 1 ELSE 0 END) as sakit,
        SUM(CASE WHEN a.status = 'A' THEN 1 ELSE 0 END) as alpha
        FROM siswa s
        JOIN kelas k ON s.kelas_id = k.id
        LEFT JOIN absensi a ON s.id = a.siswa_id AND MONTH(a.tanggal) = ? AND YEAR(a.tanggal) = ?
        WHERE s.status = 1";
if ($kelas_id > 0) $sql .= " AND s.kelas_id = $kelas_id";
$sql .= " GROUP BY s.id ORDER BY k.nama_kelas, s.nama";

$stmt = $pdo->prepare($sql);
$stmt->execute([$bulan, $tahun]);
$data = $stmt->fetchAll();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Absensi');

$sheet->setCellValue('A1', 'LAPORAN ABSENSI SISWA');
$sheet->mergeCells('A1:H1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->setCellValue('A2', 'Periode: ' . date('F Y', mktime(0,0,0,$bulan,1,$tahun)));
$sheet->mergeCells('A2:H2');

$headers = ['No', 'NIS', 'Nama', 'Kelas', 'Hadir', 'Izin', 'Sakit', 'Alpha'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col.'4', $h);
    $sheet->getStyle($col.'4')->getFont()->setBold(true);
    $col++;
}

$row = 5;
$no = 1;
foreach ($data as $d) {
    $sheet->setCellValue('A'.$row, $no++);
    $sheet->setCellValue('B'.$row, $d['nis']);
    $sheet->setCellValue('C'.$row, $d['nama']);
    $sheet->setCellValue('D'.$row, $d['nama_kelas']);
    $sheet->setCellValue('E'.$row, $d['hadir']);
    $sheet->setCellValue('F'.$row, $d['izin']);
    $sheet->setCellValue('G'.$row, $d['sakit']);
    $sheet->setCellValue('H'.$row, $d['alpha']);
    $row++;
}

foreach (range('A', 'H') as $colID) {
    $sheet->getColumnDimension($colID)->setAutoSize(true);
}

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="laporan_absensi_'.date('Ymd').'.xlsx"');
$writer->save('php://output');
exit;