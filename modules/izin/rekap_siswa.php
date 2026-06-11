<?php
$pageTitle = 'Laporan Surat Izin';
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../includes/header.php';

$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : '';
$tanggal_selesai = isset($_GET['tanggal_selesai']) ? $_GET['tanggal_selesai'] : '';

$kelas_list = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas")->fetchAll();

// Query rekap surat izin dengan rentang tanggal
$sql = "SELECT s.id, s.nis, s.nama, k.nama_kelas, COUNT(i.id) as jumlah
        FROM siswa s
        JOIN kelas k ON s.kelas_id = k.id
        LEFT JOIN surat_izin i ON s.id = i.siswa_id";

$where = [];
$params = [];

if ($kelas_id > 0) {
    $where[] = "s.kelas_id = ?";
    $params[] = $kelas_id;
}
if (!empty($tanggal_mulai) && !empty($tanggal_selesai)) {
    $where[] = "i.tanggal BETWEEN ? AND ?";
    $params[] = $tanggal_mulai;
    $params[] = $tanggal_selesai;
} elseif (!empty($tanggal_mulai)) {
    $where[] = "i.tanggal >= ?";
    $params[] = $tanggal_mulai;
} elseif (!empty($tanggal_selesai)) {
    $where[] = "i.tanggal <= ?";
    $params[] = $tanggal_selesai;
}
// Jika tidak ada filter tanggal, tidak menambah kondisi pada i.tanggal (ambil semua)

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " GROUP BY s.id ORDER BY jumlah DESC, s.nama";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rekap = $stmt->fetchAll();
?>

<div class="card mb-4">
    <div class="card-header bg-success text-white">Filter Laporan Surat Izin</div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label>Kelas</label>
                <select name="kelas_id" class="form-select">
                    <option value="0">Semua Kelas</option>
                    <?php foreach ($kelas_list as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $kelas_id == $k['id'] ? 'selected' : '' ?>><?= $k['nama_kelas'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control" value="<?= htmlspecialchars($tanggal_mulai) ?>">
            </div>
            <div class="col-md-3">
                <label>Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control" value="<?= htmlspecialchars($tanggal_selesai) ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Tampilkan</button>
                <a href="laporan.php" class="btn btn-secondary ms-2">Reset</a>
            </div>
        </form>
        <div class="form-text text-muted mt-2">
            <i class="fas fa-info-circle"></i> Kosongkan rentang tanggal untuk menampilkan semua data surat izin.
        </div>
    </div>
</div>

<?php if (!empty($rekap)): ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-info text-white">
                Rekap Surat Izin
                <?php if (!empty($tanggal_mulai) && !empty($tanggal_selesai)): ?>
                    (<?= date('d-m-Y', strtotime($tanggal_mulai)) ?> s.d <?= date('d-m-Y', strtotime($tanggal_selesai)) ?>)
                <?php elseif (!empty($tanggal_mulai)): ?>
                    (Sejak <?= date('d-m-Y', strtotime($tanggal_mulai)) ?>)
                <?php elseif (!empty($tanggal_selesai)): ?>
                    (Sampai <?= date('d-m-Y', strtotime($tanggal_selesai)) ?>)
                <?php else: ?>
                    (Semua Data)
                <?php endif; ?>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Jumlah Izin</th><th>Detail</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rekap as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['nis']) ?></td>
                            <td><?= htmlspecialchars($r['nama']) ?></td>
                            <td><?= htmlspecialchars($r['nama_kelas']) ?></td>
                            <td><?= $r['jumlah'] ?></td>
                            <td><a href="detail_siswa.php?siswa_id=<?= $r['id'] ?>&tanggal_mulai=<?= urlencode($tanggal_mulai) ?>&tanggal_selesai=<?= urlencode($tanggal_selesai) ?>" class="btn btn-sm btn-info">Detail</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-secondary text-white">Ringkasan</div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between">Total Izin: <strong><?= array_sum(array_column($rekap, 'jumlah')) ?></strong></li>
                </ul>
                <div class="mt-3">
                    <a href="export_excel.php?kelas_id=<?= $kelas_id ?>&tanggal_mulai=<?= urlencode($tanggal_mulai) ?>&tanggal_selesai=<?= urlencode($tanggal_selesai) ?>" class="btn btn-success w-100">Ekspor Excel</a>
                    <button onclick="window.print()" class="btn btn-secondary w-100 mt-2">Cetak</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-warning">Tidak ada data untuk filter yang dipilih.</div>
<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>