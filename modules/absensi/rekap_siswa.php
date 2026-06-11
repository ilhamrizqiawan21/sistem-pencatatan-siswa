<?php
$pageTitle = 'Detail Absensi Siswa';
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../includes/header.php';

$siswa_id = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;
$tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : '';
$tanggal_selesai = isset($_GET['tanggal_selesai']) ? $_GET['tanggal_selesai'] : '';

if ($siswa_id == 0) {
    // Form pilih siswa
    $siswa_list = $pdo->query("SELECT s.id, s.nis, s.nama, k.nama_kelas FROM siswa s JOIN kelas k ON s.kelas_id = k.id ORDER BY k.nama_kelas, s.nama")->fetchAll();
    ?>
    <div class="card">
        <div class="card-header bg-primary text-white">Pilih Siswa & Periode</div>
        <div class="card-body">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Siswa</label>
                        <select name="siswa_id" class="form-select" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php foreach ($siswa_list as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= $s['nis'] ?> - <?= $s['nama'] ?> (<?= $s['nama_kelas'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="<?= htmlspecialchars($tanggal_mulai) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" value="<?= htmlspecialchars($tanggal_selesai) ?>">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Tampilkan</button>
                    </div>
                </div>
            </form>
            <div class="form-text text-muted mt-2">
                <i class="fas fa-info-circle"></i> Kosongkan rentang tanggal untuk menampilkan semua data absensi.
            </div>
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

    // Query absensi dalam rentang tanggal
    $sql = "SELECT tanggal, status, keterangan FROM absensi WHERE siswa_id = ?";
    $params = [$siswa_id];
    if (!empty($tanggal_mulai) && !empty($tanggal_selesai)) {
        $sql .= " AND tanggal BETWEEN ? AND ?";
        $params[] = $tanggal_mulai;
        $params[] = $tanggal_selesai;
    } elseif (!empty($tanggal_mulai)) {
        $sql .= " AND tanggal >= ?";
        $params[] = $tanggal_mulai;
    } elseif (!empty($tanggal_selesai)) {
        $sql .= " AND tanggal <= ?";
        $params[] = $tanggal_selesai;
    }
    $sql .= " ORDER BY tanggal ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $absensi = $stmt->fetchAll();

    // Buat array mapping tanggal => status, keterangan
    $absensi_map = [];
    foreach ($absensi as $a) {
        $absensi_map[$a['tanggal']] = ['status' => $a['status'], 'keterangan' => $a['keterangan']];
    }

    // Tentukan rentang tanggal untuk ditampilkan (jika filter kosong, ambil dari data absensi)
    if (empty($tanggal_mulai) && empty($tanggal_selesai)) {
        if (!empty($absensi)) {
            $tanggal_mulai = min(array_keys($absensi_map));
            $tanggal_selesai = max(array_keys($absensi_map));
        } else {
            $tanggal_mulai = date('Y-m-d');
            $tanggal_selesai = date('Y-m-d');
        }
    } elseif (empty($tanggal_mulai)) {
        $tanggal_mulai = $tanggal_selesai;
    } elseif (empty($tanggal_selesai)) {
        $tanggal_selesai = $tanggal_mulai;
    }

    // Generate semua tanggal dalam rentang
    $period = new DatePeriod(
        new DateTime($tanggal_mulai),
        new DateInterval('P1D'),
        (new DateTime($tanggal_selesai))->modify('+1 day')
    );
    $tanggal_list = [];
    foreach ($period as $date) {
        $tanggal_list[] = $date->format('Y-m-d');
    }
    ?>
    <div class="card">
        <div class="card-header bg-info text-white">
            Detail Absensi: <?= htmlspecialchars($siswa['nama']) ?> (<?= $siswa['nis'] ?>) - Kelas <?= $siswa['nama_kelas'] ?>
            <br><small>Periode: <?= date('d-m-Y', strtotime($tanggal_mulai)) ?> s.d <?= date('d-m-Y', strtotime($tanggal_selesai)) ?></small>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr><th>Tanggal</th><th>Status</th><th>Keterangan</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($tanggal_list as $tgl): 
                        $absen = $absensi_map[$tgl] ?? null;
                        $status = $absen ? $absen['status'] : '-';
                        $keterangan = $absen ? $absen['keterangan'] : '-';
                        $badge = '';
                        if ($status == 'H') $badge = 'bg-success text-white';
                        elseif ($status == 'I') $badge = 'bg-warning';
                        elseif ($status == 'S') $badge = 'bg-info text-white';
                        elseif ($status == 'A') $badge = 'bg-danger text-white';
                        else $badge = 'bg-secondary text-white';
                    ?>
                        <tr>
                            <td><?= date('d-m-Y', strtotime($tgl)) ?></td>
                            <td><span class="badge <?= $badge ?> px-3 py-2"><?= $status ?></span></td>
                            <td><?= htmlspecialchars($keterangan) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <a href="rekap_siswa.php" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
    <?php
}
require_once '../../includes/footer.php';
?>