<?php
$pageTitle = 'Detail Keterlambatan Siswa';
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../includes/header.php';

$siswa_id = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

if ($siswa_id == 0) {
    // Form pilih siswa
    $siswa_list = $pdo->query("SELECT s.id, s.nis, s.nama, k.nama_kelas FROM siswa s JOIN kelas k ON s.kelas_id = k.id WHERE s.status = 1 ORDER BY k.nama_kelas, s.nama")->fetchAll();
    ?>
    <div class="card">
        <div class="card-header">Pilih Siswa</div>
        <div class="card-body">
            <form method="GET">
                <div class="row">
                    <div class="col-md-6">
                        <select name="siswa_id" class="form-select" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php foreach ($siswa_list as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= $s['nis'] ?> - <?= htmlspecialchars($s['nama']) ?> (<?= $s['nama_kelas'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="bulan" class="form-select">
                            <?php for ($m=1;$m<=12;$m++): ?>
                                <option value="<?= $m ?>" <?= $bulan==$m ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="tahun" class="form-select">
                            <?php $thn=date('Y'); for($y=$thn-2;$y<=$thn+1;$y++): ?>
                                <option value="<?= $y ?>" <?= $tahun==$y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Tampilkan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
} else {
    // Ambil data siswa
    $stmt = $pdo->prepare("SELECT s.*, k.nama_kelas FROM siswa s JOIN kelas k ON s.kelas_id = k.id WHERE s.id = ?");
    $stmt->execute([$siswa_id]);
    $siswa = $stmt->fetch();
    if (!$siswa) {
        echo "<div class='alert alert-danger'>Siswa tidak ditemukan.</div>";
        require_once '../../includes/footer.php';
        exit;
    }

    // Ambil daftar keterlambatan dalam bulan/tahun
    $start_date = "$tahun-$bulan-01";
    $end_date = date("Y-m-t", strtotime($start_date));
    $stmt = $pdo->prepare("SELECT * FROM keterlambatan WHERE siswa_id = ? AND tanggal BETWEEN ? AND ? ORDER BY tanggal DESC");
    $stmt->execute([$siswa_id, $start_date, $end_date]);
    $keterlambatan = $stmt->fetchAll();
    ?>
    <div class="card">
        <div class="card-header bg-info text-white">Detail Keterlambatan: <?= htmlspecialchars($siswa['nama']) ?> (<?= $siswa['nis'] ?>) - Kelas <?= $siswa['nama_kelas'] ?></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr><th>Tanggal</th><th>Jam Datang</th><th>Alasan</th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($keterlambatan) > 0): ?>
                            <?php foreach ($keterlambatan as $k): ?>
                            <tr>
                                <td><?= date('d-m-Y', strtotime($k['tanggal'])) ?></td>
                                <td><?= $k['jam_datang'] ?></td>
                                <td><?= htmlspecialchars($k['alasan']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center">Tidak ada keterlambatan pada periode ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <a href="laporan.php?kelas_id=<?= $siswa['kelas_id'] ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="btn btn-secondary">Kembali ke Laporan</a>
        </div>
    </div>
    <?php
}
require_once '../../includes/footer.php';
?>