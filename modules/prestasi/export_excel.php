<?php
require_once '../../vendor/autoload.php';
require_once '../../config/db.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

$sql = "SELECT s.nis, s.nama, k.nama_kelas, p.nama_prestasi, t.nama as tingkat, p.juara, p.tanggal, p.penyelenggara
        FROM prestasi p
        JOIN siswa s ON p.siswa_id = s.id
        JOIN kelas k ON s.kelas_id = k.id
        JOIN tingkat_prestasi t ON p.tingkat_prestasi_id = t.id
        WHERE YEAR(p.tanggal) = ?";
if ($kelas_id > 0) $sql .= " AND s.kelas_id = $kelas_id";
$sql .= " ORDER BY p.tanggal DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$tahun]);
$data = $stmt->fetchAll();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Prestasi Siswa');
$sheet->setCellValue('A1', 'LAPORAN PRESTASI SISWA TAHUN ' . $tahun);
$sheet->mergeCells('A1:H1');
$headers = ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Nama Prestasi', 'Tingkat', 'Juara', 'Tanggal', 'Penyelenggara'];
$col = 'A';
foreach ($headers as $h) { $sheet->setCellValue($col.'3', $h); $sheet->getStyle($col.'3')->getFont()->setBold(true); $col++; }
$row = 4; $no = 1;
foreach ($data as $d) {
    $sheet->setCellValue('A'.$row, $no++);
    $sheet->setCellValue('B'.$row, $d['nis']);
    $sheet->setCellValue('C'.$row, $d['nama']);
    $sheet->setCellValue('D'.$row, $d['nama_kelas']);
    $sheet->setCellValue('E'.$row, $d['nama_prestasi']);
    $sheet->setCellValue('F'.$row, $d['tingkat']);
    $sheet->setCellValue('G'.$row, $d['juara']);
    $sheet->setCellValue('H'.$row, date('d-m-Y', strtotime($d['tanggal'])));
    $sheet->setCellValue('I'.$row, $d['penyelenggara']);
    $row++;
}
foreach (range('A', 'I') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="laporan_prestasi_'.$tahun.'.xlsx"');
$writer->save('php://output');
exit;