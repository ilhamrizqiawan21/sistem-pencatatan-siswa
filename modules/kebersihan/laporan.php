<?php
$pageTitle = 'Laporan Kebersihan';
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../includes/header.php';

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$kelas_list = $pdo->query("SELECT * FROM kelas")->fetchAll();

$sql = "SELECT k.nama_kelas, AVG(kb.nilai_total) as rata, COUNT(kb.id) as jumlah_hari
        FROM kebersihan_kelas kb
        JOIN kelas k ON kb.kelas_id = k.id
        WHERE kb.tahun_ajaran_id = (SELECT id FROM tahun_ajaran WHERE is_aktif=1 LIMIT 1)
        AND MONTH(kb.tanggal) = ? AND YEAR(kb.tanggal) = ?
        GROUP BY kb.kelas_id
        ORDER BY rata DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$bulan, $tahun]);
$rekap = $stmt->fetchAll();
?>
<div class="card mb-4"><div class="card-header bg-success text-white">Laporan Kebersihan Kelas</div><div class="card-body">
<form method="GET" class="row g-3"><div class="col-md-3"><select name="bulan" class="form-select"><?php for($m=1;$m<=12;$m++): ?><option value="<?= $m ?>" <?= $bulan==$m?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option><?php endfor; ?></select></div>
<div class="col-md-3"><select name="tahun" class="form-select"><?php for($y=date('Y')-2;$y<=date('Y')+1;$y++): ?><option value="<?= $y ?>" <?= $tahun==$y?'selected':'' ?>><?= $y ?></option><?php endfor; ?></select></div>
<div class="col-md-2"><button type="submit" class="btn btn-primary">Tampilkan</button></div></form></div></div>

<div class="row"><div class="col-md-12"><table class="table table-bordered"><thead class="table-dark"><tr><th>Kelas</th><th>Rata-rata Nilai</th><th>Jumlah Hari Penilaian</th><th>Status</th></tr></thead><tbody>
<?php foreach($rekap as $r): ?>
<?php $status = $r['rata'] >= 85 ? 'Baik' : ($r['rata'] >= 70 ? 'Cukup' : 'Perlu Perbaikan'); ?>
<tr><td><?= $r['nama_kelas'] ?></td><td><?= round($r['rata']) ?></td><td><?= $r['jumlah_hari'] ?></td><td><?= $status ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></div>
<?php require_once '../../includes/footer.php'; ?>