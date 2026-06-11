<?php
$pageTitle = 'Laporan Pelanggaran';
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../includes/header.php';

$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

$kelas_list = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas")->fetchAll();

// Rekap total poin pelanggaran per siswa
$sql = "SELECT s.id, s.nis, s.nama, k.nama_kelas, SUM(jp.poin) as total_poin, COUNT(p.id) as jumlah_pelanggaran
        FROM siswa s
        JOIN kelas k ON s.kelas_id = k.id
        LEFT JOIN pelanggaran p ON s.id = p.siswa_id AND MONTH(p.tanggal)=? AND YEAR(p.tanggal)=?
        LEFT JOIN jenis_pelanggaran jp ON p.jenis_pelanggaran_id = jp.id
        WHERE s.status = 1";
if ($kelas_id > 0) $sql .= " AND s.kelas_id = $kelas_id";
$sql .= " GROUP BY s.id ORDER BY total_poin DESC, jumlah_pelanggaran DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$bulan, $tahun]);
$rekap = $stmt->fetchAll();

// Top 10 poin terbanyak
$top10 = array_slice($rekap, 0, 10);
?>

<div class="card mb-4">
    <div class="card-header bg-success text-white">Filter Laporan Pelanggaran</div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label>Kelas</label>
                <select name="kelas_id" class="form-select">
                    <option value="0">Semua Kelas</option>
                    <?php foreach ($kelas_list as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $kelas_id == $k['id'] ? 'selected' : '' ?>><?= $k['nama_kelas'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Bulan</label>
                <select name="bulan" class="form-select">
                    <?php for ($m=1;$m<=12;$m++): ?>
                        <option value="<?= $m ?>" <?= $bulan==$m ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Tahun</label>
                <select name="tahun" class="form-select">
                    <?php $thn=date('Y'); for($y=$thn-2;$y<=$thn+1;$y++): ?>
                        <option value="<?= $y ?>" <?= $tahun==$y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($rekap)): ?>
<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-info text-white">Rekap Pelanggaran <?= date('F Y', mktime(0,0,0,$bulan,1,$tahun)) ?></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Jumlah Pelanggaran</th><th>Total Poin</th><th>Detail</th></td>
                    </thead>
                    <tbody>
                        <?php foreach ($rekap as $r): ?>
                        <tr>
                            <td><?= $r['nis'] ?></td>
                            <td><?= htmlspecialchars($r['nama']) ?></td>
                            <td><?= $r['nama_kelas'] ?></td>
                            <td><?= $r['jumlah_pelanggaran'] ?></td>
                            <td><?= $r['total_poin'] ?></td>
                            <td><a href="rekap_siswa.php?siswa_id=<?= $r['id'] ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="btn btn-sm btn-info">Detail</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-warning">Grafik 10 Siswa dengan Poin Tertinggi</div>
            <div class="card-body">
                <canvas id="poinChart" height="300"></canvas>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header bg-secondary text-white">Ringkasan</div>
            <div class="card-body">
                <?php
                $total_poin = array_sum(array_column($rekap, 'total_poin'));
                $total_pelanggaran = array_sum(array_column($rekap, 'jumlah_pelanggaran'));
                ?>
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between">Total Poin Pelanggaran: <strong><?= $total_poin ?></strong></li>
                    <li class="list-group-item d-flex justify-content-between">Total Kejadian: <strong><?= $total_pelanggaran ?></strong></li>
                </ul>
            </div>
        </div>
        <div class="mt-3">
            <a href="export_excel.php?kelas_id=<?= $kelas_id ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="btn btn-success w-100"><i class="fas fa-file-excel"></i> Ekspor ke Excel</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('poinChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($top10, 'nama')) ?>,
            datasets: [{
                label: 'Total Poin',
                data: <?= json_encode(array_column($top10, 'total_poin')) ?>,
                backgroundColor: '#EF4444'
            }]
        }
    });
</script>
<?php else: ?>
<div class="alert alert-warning">Tidak ada data pelanggaran untuk filter ini.</div>
<?php endif; ?>
<?php require_once '../../includes/footer.php'; ?>