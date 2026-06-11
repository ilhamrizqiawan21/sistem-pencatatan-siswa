<?php
require_once '../../vendor/autoload.php';
require_once '../../config/db.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

$sql = "SELECT s.nis, s.nama, k.nama_kelas, SUM(jp.poin) as total_poin, COUNT(p.id) as jumlah
        FROM siswa s
        JOIN kelas k ON s.kelas_id = k.id
        LEFT JOIN pelanggaran p ON s.id = p.siswa_id AND MONTH(p.tanggal)=? AND YEAR(p.tanggal)=?
        LEFT JOIN jenis_pelanggaran jp ON p.jenis_pelanggaran_id = jp.id
        WHERE s.status = 1";
if ($kelas_id > 0) $sql .= " AND s.kelas_id = $kelas_id";
$sql .= " GROUP BY s.id ORDER BY total_poin DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$bulan, $tahun]);
$data = $stmt->fetchAll();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Pelanggaran');
$sheet->setCellValue('A1', 'LAPORAN PELANGGARAN SISWA');
$sheet->mergeCells('A1:E1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->setCellValue('A2', 'Periode: ' . date('F Y', mktime(0,0,0,$bulan,1,$tahun)));
$sheet->mergeCells('A2:E2');

$headers = ['No', 'NIS', 'Nama', 'Kelas', 'Jumlah Pelanggaran', 'Total Poin'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col.'4', $h);
    $sheet->getStyle($col.'4')->getFont()->setBold(true);
    $col++;
}
$row = 5; $no = 1;
foreach ($data as $d) {
    $sheet->setCellValue('A'.$row, $no++);
    $sheet->setCellValue('B'.$row, $d['nis']);
    $sheet->setCellValue('C'.$row, $d['nama']);
    $sheet->setCellValue('D'.$row, $d['nama_kelas']);
    $sheet->setCellValue('E'.$row, $d['jumlah']);
    $sheet->setCellValue('F'.$row, $d['total_poin']);
    $row++;
}
foreach (range('A', 'F') as $colID) $sheet->getColumnDimension($colID)->setAutoSize(true);
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="laporan_pelanggaran_'.date('Ymd').'.xlsx"');
$writer->save('php://output');
exit;