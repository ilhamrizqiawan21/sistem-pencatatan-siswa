<?php
$pageTitle = 'Laporan Prestasi';
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../includes/header.php';

$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$kelas_list = $pdo->query("SELECT * FROM kelas")->fetchAll();

$sql = "SELECT s.nis, s.nama, k.nama_kelas, p.nama_prestasi, t.nama as tingkat, p.juara, p.tanggal, p.penyelenggara
        FROM prestasi p
        JOIN siswa s ON p.siswa_id = s.id
        JOIN kelas k ON s.kelas_id = k.id
        JOIN tingkat_prestasi t ON p.tingkat_prestasi_id = t.id
        WHERE YEAR(p.tanggal) = ?";
$params = [$tahun];
if ($kelas_id > 0) {
    $sql .= " AND s.kelas_id = ?";
    $params[] = $kelas_id;
}
$sql .= " ORDER BY p.tanggal DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$prestasi = $stmt->fetchAll();

// Statistik
$total_prestasi = count($prestasi);
$by_tingkat = $pdo->prepare("SELECT t.nama, COUNT(p.id) as jumlah FROM prestasi p JOIN tingkat_prestasi t ON p.tingkat_prestasi_id = t.id WHERE YEAR(p.tanggal)=? GROUP BY t.id");
$by_tingkat->execute([$tahun]);
$tingkat_stats = $by_tingkat->fetchAll();
?>
<div class="card mb-4"><div class="card-header bg-success text-white">Filter Laporan Prestasi</div><div class="card-body">
<form method="GET" class="row g-3">
    <div class="col-md-4"><label>Kelas</label><select name="kelas_id" class="form-select"><option value="0">Semua Kelas</option><?php foreach($kelas_list as $k): ?><option value="<?= $k['id'] ?>" <?= $kelas_id==$k['id']?'selected':'' ?>><?= $k['nama_kelas'] ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label>Tahun</label><select name="tahun" class="form-select"><?php for($y=date('Y')-2;$y<=date('Y')+1;$y++): ?><option value="<?= $y ?>" <?= $tahun==$y?'selected':'' ?>><?= $y ?></option><?php endfor; ?></select></div>
    <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary">Tampilkan</button></div>
</form></div></div>

<div class="row"><div class="col-md-8"><div class="card"><div class="card-header bg-info text-white">Daftar Prestasi <?= $tahun ?></div><div class="card-body table-responsive"><table class="table table-bordered"><thead class="table-dark"><tr><th>NIS</th><th>Siswa</th><th>Kelas</th><th>Prestasi</th><th>Tingkat</th><th>Juara</th><th>Tanggal</th><th>Penyelenggara</th></tr></thead><tbody>
<?php foreach($prestasi as $p): ?><tr><td><?= $p['nis'] ?></td><td><?= htmlspecialchars($p['nama']) ?></td><td><?= $p['nama_kelas'] ?></td><td><?= htmlspecialchars($p['nama_prestasi']) ?></td><td><?= $p['tingkat'] ?></td><td><?= $p['juara'] ?></td><td><?= date('d-m-Y',strtotime($p['tanggal'])) ?></td><td><?= htmlspecialchars($p['penyelenggara']) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div></div>
<div class="col-md-4"><div class="card"><div class="card-header bg-warning">Statistik</div><div class="card-body"><p>Total Prestasi: <strong><?= $total_prestasi ?></strong></p><p>Berdasarkan Tingkat:</p><ul><?php foreach($tingkat_stats as $ts): ?><li><?= $ts['nama'] ?>: <?= $ts['jumlah'] ?></li><?php endforeach; ?></ul></div></div>
<div class="mt-3"><a href="export_excel.php?kelas_id=<?= $kelas_id ?>&tahun=<?= $tahun ?>" class="btn btn-success w-100">Ekspor Excel</a></div></div></div>
<?php require_once '../../includes/footer.php'; ?>