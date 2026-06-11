<?php
$mod = $_GET['mod'] ?? '';
$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$siswa_id = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;

$tahun_mulai = isset($_GET['tahun_mulai']) ? (int)$_GET['tahun_mulai'] : 0;
$bulan_mulai = isset($_GET['bulan_mulai']) ? (int)$_GET['bulan_mulai'] : 0;
$tahun_selesai = isset($_GET['tahun_selesai']) ? (int)$_GET['tahun_selesai'] : 0;
$bulan_selesai = isset($_GET['bulan_selesai']) ? (int)$_GET['bulan_selesai'] : 0;

if (!$mod) die('Pilih jenis data terlebih dahulu.');

require_once '../../config/db.php';

// Bangun keterangan rentang untuk ditampilkan
$range_text = '';
if ($tahun_mulai && $bulan_mulai && $tahun_selesai && $bulan_selesai) {
    $range_text = sprintf(" | Rentang: %s %d s.d %s %d",
        date('F', mktime(0,0,0,$bulan_mulai,1)),
        $tahun_mulai,
        date('F', mktime(0,0,0,$bulan_selesai,1)),
        $tahun_selesai
    );
} elseif ($tahun_mulai && $bulan_mulai) {
    $range_text = sprintf(" | Sejak: %s %d", date('F', mktime(0,0,0,$bulan_mulai,1)), $tahun_mulai);
} elseif ($tahun_selesai && $bulan_selesai) {
    $range_text = sprintf(" | Sampai: %s %d", date('F', mktime(0,0,0,$bulan_selesai,1)), $tahun_selesai);
}

// Bangun kondisi WHERE untuk tanggal
$tanggal_where = '';
$tanggal_params = [];
if ($tahun_mulai && $bulan_mulai && $tahun_selesai && $bulan_selesai) {
    $start = sprintf("%04d-%02d-01", $tahun_mulai, $bulan_mulai);
    $end = date("Y-m-t", strtotime(sprintf("%04d-%02d-01", $tahun_selesai, $bulan_selesai)));
    $tanggal_where = " AND tanggal BETWEEN ? AND ? ";
    $tanggal_params = [$start, $end];
} elseif ($tahun_mulai && $bulan_mulai) {
    $start = sprintf("%04d-%02d-01", $tahun_mulai, $bulan_mulai);
    $tanggal_where = " AND tanggal >= ? ";
    $tanggal_params = [$start];
} elseif ($tahun_selesai && $bulan_selesai) {
    $end = date("Y-m-t", strtotime(sprintf("%04d-%02d-01", $tahun_selesai, $bulan_selesai)));
    $tanggal_where = " AND tanggal <= ? ";
    $tanggal_params = [$end];
}

function getFilterWhere($kelas_id, $siswa_id, array &$params) {
    $w = [];
    if ($kelas_id > 0) {
        $w[] = "s.kelas_id = ?";
        $params[] = $kelas_id;
    }
    if ($siswa_id > 0) {
        $w[] = "s.id = ?";
        $params[] = $siswa_id;
    }
    return empty($w) ? '' : ' AND ' . implode(' AND ', $w);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan <?= ucfirst($mod) ?></title>
    <style>
        body{font-family:'Segoe UI',Arial,sans-serif;padding:20px;background:white;color:#333}
        .header{text-align:center;margin-bottom:20px;border-bottom:2px solid #3B82F6;padding-bottom:10px}
        .header h1{margin:0;font-size:24px}
        .header p{margin:5px 0;color:#555}
        table{width:100%;border-collapse:collapse;margin-top:20px}
        th,td{border:1px solid #ccc;padding:8px;text-align:left}
        th{background-color:#3B82F6;color:white}
        tr:nth-child(even){background-color:#f9f9f9}
        .footer{margin-top:30px;text-align:center;font-size:12px;color:#777;border-top:1px solid #ccc;padding-top:10px}
        @media print{body{padding:0;margin:0}.no-print{display:none}th{background-color:#ccc;color:black}}
        button{margin-bottom:20px;padding:8px 16px;background:#3B82F6;color:white;border:none;cursor:pointer;border-radius:4px}
        button:hover{background:#2563EB}
    </style>
</head>
<body>
<div class="no-print" style="text-align:right;margin-bottom:10px">
    <button onclick="window.print()">🖨️ Cetak / Print</button>
    <button onclick="window.close()">❌ Tutup</button>
</div>
<div class="header">
    <h1>LAPORAN <?= strtoupper($mod) ?></h1>
    <p>MTs Al-Ihsan | Dicetak: <?= date('d-m-Y H:i:s') ?></p>
    <p>Filter: Kelas=<?= $kelas_id>0?"ID $kelas_id":"Semua" ?> | Siswa=<?= $siswa_id>0?"ID $siswa_id":"Semua" ?><?= $range_text ?></p>
</div>

<?php
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
    echo '<table><thead><tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Pelanggaran</th><th>Poin</th><th>Tanggal</th><th>Keterangan</th></tr></thead><tbody>';
    foreach ($data as $d) echo "<tr><td>{$d['nis']}</td><td>{$d['nama']}</td><td>{$d['nama_kelas']}</td><td>{$d['pelanggaran']}</td><td>{$d['poin']}</td><td>{$d['tanggal']}</td><td>{$d['keterangan']}</td></tr>";
    echo '</tbody></table>';
}
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
    echo '<table><thead><tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Tanggal</th><th>Jam Datang</th><th>Alasan</th></tr></thead><tbody>';
    foreach ($data as $d) echo "<tr><td>{$d['nis']}</td><td>{$d['nama']}</td><td>{$d['nama_kelas']}</td><td>{$d['tanggal']}</td><td>{$d['jam_datang']}</td><td>{$d['alasan']}</td></tr>";
    echo '</tbody></table>';
}
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
    echo '<table><thead><tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Prestasi</th><th>Tingkat</th><th>Juara</th><th>Tanggal</th><th>Penyelenggara</th></tr></thead><tbody>';
    foreach ($data as $d) echo "<tr><td>{$d['nis']}</td><td>{$d['nama']}</td><td>{$d['nama_kelas']}</td><td>{$d['nama_prestasi']}</td><td>{$d['tingkat']}</td><td>{$d['juara']}</td><td>{$d['tanggal']}</td><td>{$d['penyelenggara']}</td></tr>";
    echo '</tbody></table>';
}
elseif ($mod == 'absensi') {
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
    $tgl_condition = "";
    if ($tanggal_where) {
        $tgl_condition = str_replace("AND tanggal", "AND a.tanggal", $tanggal_where);
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
    echo '<table><thead><tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpha</th></tr></thead><tbody>';
    foreach ($data as $d) echo "<tr><td>{$d['nis']}</td><td>{$d['nama']}</td><td>{$d['nama_kelas']}</td><td>{$d['hadir']}</td><td>{$d['izin']}</td><td>{$d['sakit']}</td><td>{$d['alpha']}</td></tr>";
    echo '</tbody></table>';
}
elseif ($mod == 'siswa') {
    $params = [];
    $sql = "SELECT s.nis, s.nama, k.nama_kelas, s.jenis_kelamin
            FROM siswa s
            JOIN kelas k ON s.kelas_id = k.id
            WHERE s.status=1";
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
    echo '<table><thead><tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Jenis Kelamin</th></tr></thead><tbody>';
    foreach ($data as $d) echo "<tr><td>{$d['nis']}</td><td>{$d['nama']}</td><td>{$d['nama_kelas']}</td><td>" . ($d['jenis_kelamin']=='L'?'Laki-laki':'Perempuan') . "</td></tr>";
    echo '</tbody></table>';
}
elseif ($mod == 'kelas') {
    $stmt = $pdo->prepare("SELECT id, nama_kelas, wali_kelas, created_at FROM kelas ORDER BY nama_kelas");
    $stmt->execute();
    $data = $stmt->fetchAll();
    echo '<table><thead><tr><th>ID</th><th>Nama Kelas</th><th>Wali Kelas</th><th>Tanggal Dibuat</th></tr></thead><tbody>';
    foreach ($data as $d) echo "<tr><td>{$d['id']}</td><td>{$d['nama_kelas']}</td><td>{$d['wali_kelas']}</td><td>" . date('d-m-Y', strtotime($d['created_at'])) . "</td></tr>";
    echo '</tbody></table>';
} else {
    echo '<div style="color:red;">Modul tidak valid</div>';
}
?>

<div class="footer">
    Dicetak pada: <?= date('d-m-Y H:i:s') ?> | © MTs Al-Ihsan
</div>
</body>
</html>