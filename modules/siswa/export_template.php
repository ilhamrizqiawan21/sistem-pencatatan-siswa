<?php
require_once '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Template Siswa');

// Header kolom hanya 4
$headers = ['NIS', 'Nama Lengkap', 'Kelas (Nama Kelas)', 'Jenis Kelamin (L/P)'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . '1', $h);
    $sheet->getStyle($col . '1')->getFont()->setBold(true);
    $sheet->getStyle($col . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
    $col++;
}

// Contoh data
$sheet->setCellValue('A2', '12345');
$sheet->setCellValue('B2', 'Ahmad Fauzi');
$sheet->setCellValue('C2', 'VII A');
$sheet->setCellValue('D2', 'L');

// Auto size kolom
foreach (range('A', 'D') as $colID) {
    $sheet->getColumnDimension($colID)->setAutoSize(true);
}

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="template_import_siswa.xlsx"');
$writer->save('php://output');
exit;