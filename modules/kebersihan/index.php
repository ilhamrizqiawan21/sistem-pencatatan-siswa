<?php
$pageTitle = 'Kebersihan Kelas';
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../includes/header.php';

$tahun_aktif = getTahunAjaranAktif($pdo);
$ta_id = $tahun_aktif['id'] ?? 0;

$kelas_list = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas")->fetchAll();
$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

$data_penilaian = null;
if ($kelas_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM kebersihan_kelas WHERE kelas_id = ? AND tanggal = ? AND tahun_ajaran_id = ?");
    $stmt->execute([$kelas_id, $tanggal, $ta_id]);
    $data_penilaian = $stmt->fetch();
}

// Grafik rata-rata kebersihan per kelas (bulan ini)
$bulan_ini = date('m');
$tahun_ini = date('Y');
$sql_rata = "SELECT k.nama_kelas, AVG(kb.nilai_total) as rata
             FROM kebersihan_kelas kb
             JOIN kelas k ON kb.kelas_id = k.id
             WHERE kb.tahun_ajaran_id = ? AND MONTH(kb.tanggal) = ? AND YEAR(kb.tanggal) = ?
             GROUP BY kb.kelas_id
             ORDER BY rata DESC";
$stmt_rata = $pdo->prepare($sql_rata);
$stmt_rata->execute([$ta_id, $bulan_ini, $tahun_ini]);
$rata_kelas = $stmt_rata->fetchAll();
?>
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Form Penilaian Kebersihan Kelas</div>
            <div class="card-body">
                <form method="GET" class="row g-2 mb-3">
                    <div class="col-md-5">
                        <select name="kelas_id" class="form-select" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($kelas_list as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= $kelas_id == $k['id'] ? 'selected' : '' ?>><?= $k['nama_kelas'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="date" name="tanggal" class="form-control" value="<?= $tanggal ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-secondary w-100">Cek Data</button>
                    </div>
                </form>

                <?php if ($kelas_id > 0): ?>
                <form method="POST" action="proses.php">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                    <input type="hidden" name="kelas_id" value="<?= $kelas_id ?>">
                    <input type="hidden" name="tanggal" value="<?= $tanggal ?>">
                    <input type="hidden" name="tahun_ajaran_id" value="<?= $ta_id ?>">
                    <div class="mb-3">
                        <label>Kebersihan Lantai (0-100)</label>
                        <input type="number" name="nilai_lantai" class="form-control" value="<?= $data_penilaian['nilai_lantai'] ?? 0 ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Kebersihan Tempat Sampah</label>
                        <input type="number" name="nilai_sampah" class="form-control" value="<?= $data_penilaian['nilai_sampah'] ?? 0 ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Rak Kelas</label>
                        <input type="number" name="nilai_rak" class="form-control" value="<?= $data_penilaian['nilai_rak'] ?? 0 ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Penataan Kelas</label>
                        <input type="number" name="nilai_penataan" class="form-control" value="<?= $data_penilaian['nilai_penataan'] ?? 0 ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control"><?= htmlspecialchars($data_penilaian['keterangan'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Simpan Nilai</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">Rata-rata Kebersihan per Kelas (Bulan <?= date('F') ?>)</div>
            <div class="card-body">
                <canvas id="kebersihanChart" height="300"></canvas>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header bg-secondary text-white">Riwayat Penilaian</div>
            <div class="card-body table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Kelas</th><th>Tanggal</th><th>Nilai Total</th></tr></thead>
                    <tbody>
                    <?php
                    $riwayat = $pdo->query("SELECT k.nama_kelas, kb.tanggal, kb.nilai_total FROM kebersihan_kelas kb JOIN kelas k ON kb.kelas_id = k.id WHERE kb.tahun_ajaran_id=$ta_id ORDER BY kb.tanggal DESC LIMIT 10")->fetchAll();
                    foreach ($riwayat as $r): ?>
                    <tr><td><?= $r['nama_kelas'] ?></td><td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td><td><?= $r['nilai_total'] ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const labels = <?= json_encode(array_column($rata_kelas, 'nama_kelas')) ?>;
    const data = <?= json_encode(array_column($rata_kelas, 'rata')) ?>;
    new Chart(document.getElementById('kebersihanChart'), {
        type: 'bar',
        data: { labels: labels, datasets: [{ label: 'Rata-rata Nilai', data: data, backgroundColor: '#3B82F6' }] }
    });
</script>
<?php require_once '../../includes/footer.php'; ?>