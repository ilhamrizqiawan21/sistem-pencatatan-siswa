<?php
$pageTitle = 'Laporan Absensi';
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../includes/header.php';

// ── Sanitasi input ────────────────────────────────────────────
$kelas_id        = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$tanggal_mulai   = isset($_GET['tanggal_mulai'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tanggal_mulai'])
                   ? $_GET['tanggal_mulai'] : '';
$tanggal_selesai = isset($_GET['tanggal_selesai']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tanggal_selesai'])
                   ? $_GET['tanggal_selesai'] : '';

// Pastikan range logis
if ($tanggal_mulai && $tanggal_selesai && $tanggal_mulai > $tanggal_selesai) {
    [$tanggal_mulai, $tanggal_selesai] = [$tanggal_selesai, $tanggal_mulai];
}

$kelas_list = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas")->fetchAll();
$rekap      = [];

$filter_aktif = $kelas_id > 0 || $tanggal_mulai || $tanggal_selesai || isset($_GET['kelas_id']);

if ($filter_aktif) {
    // ── Query rekap ───────────────────────────────────────────
    $sql = "SELECT s.id, s.nis, s.nama, k.nama_kelas,
                   SUM(a.status = 'H') AS hadir,
                   SUM(a.status = 'I') AS izin,
                   SUM(a.status = 'S') AS sakit,
                   SUM(a.status = 'A') AS alpha,
                   COUNT(a.id)         AS total
            FROM siswa s
            JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN absensi a ON s.id = a.siswa_id";

    $where  = ["s.status = 1"];
    $params = [];

    if ($kelas_id > 0) {
        $where[]  = "s.kelas_id = ?";
        $params[] = $kelas_id;
    }
    if ($tanggal_mulai && $tanggal_selesai) {
        $where[]  = "a.tanggal BETWEEN ? AND ?";
        $params[] = $tanggal_mulai;
        $params[] = $tanggal_selesai;
    } elseif ($tanggal_mulai) {
        $where[]  = "a.tanggal >= ?";
        $params[] = $tanggal_mulai;
    } elseif ($tanggal_selesai) {
        $where[]  = "a.tanggal <= ?";
        $params[] = $tanggal_selesai;
    }

    $sql .= " WHERE " . implode(" AND ", $where);
    $sql .= " GROUP BY s.id ORDER BY k.nama_kelas, s.nama";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rekap = $stmt->fetchAll();
}

// ── Agregat ───────────────────────────────────────────────────
$total_hadir = array_sum(array_column($rekap, 'hadir'));
$total_izin  = array_sum(array_column($rekap, 'izin'));
$total_sakit = array_sum(array_column($rekap, 'sakit'));
$total_alpha = array_sum(array_column($rekap, 'alpha'));
$grand_total = $total_hadir + $total_izin + $total_sakit + $total_alpha;
$pct_hadir   = $grand_total > 0 ? round(($total_hadir / $grand_total) * 100, 1) : 0;

// Top 5 alpha
$top_alpha = $rekap;
usort($top_alpha, fn($a, $b) => $b['alpha'] - $a['alpha']);
$top_alpha = array_filter(array_slice($top_alpha, 0, 5), fn($r) => $r['alpha'] > 0);

// Label periode
function periode_label($mulai, $selesai): string {
    if ($mulai && $selesai) return date('d M Y', strtotime($mulai)) . ' — ' . date('d M Y', strtotime($selesai));
    if ($mulai) return 'Sejak ' . date('d M Y', strtotime($mulai));
    if ($selesai) return 'Sampai ' . date('d M Y', strtotime($selesai));
    return 'Semua Data';
}
?>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-2 text-primary"></i> Filter Laporan Absensi
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Kelas</label>
                <select name="kelas_id" class="form-select">
                    <option value="0">Semua Kelas</option>
                    <?php foreach ($kelas_list as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $kelas_id == $k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control"
                       value="<?= htmlspecialchars($tanggal_mulai) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control"
                       value="<?= htmlspecialchars($tanggal_selesai) ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fas fa-search me-1"></i> Tampilkan
                </button>
                <a href="laporan.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
        <div class="form-text mt-2">
            <i class="fas fa-info-circle text-muted me-1"></i>
            Kosongkan rentang tanggal untuk menampilkan semua data absensi.
        </div>
    </div>
</div>

<?php if (!$filter_aktif): ?>
<div class="alert alert-info d-flex align-items-center gap-2">
    <i class="fas fa-hand-pointer"></i>
    Pilih filter di atas lalu klik <strong>Tampilkan</strong> untuk melihat laporan.
</div>

<?php elseif (empty($rekap)): ?>
<div class="alert alert-warning d-flex align-items-center gap-2">
    <i class="fas fa-search"></i> Tidak ada data untuk filter yang dipilih.
</div>

<?php else: ?>

<!-- Ringkasan stat cards -->
<div class="row g-3 mb-4">
    <?php
    $stats = [
        ['label'=>'Total Hadir', 'val'=>$total_hadir, 'sub'=>"$pct_hadir% kehadiran",     'icon'=>'fa-check-circle',  'color'=>'#22c55e','bg'=>'#f0fdf4'],
        ['label'=>'Total Izin',  'val'=>$total_izin,  'sub'=>'',                           'icon'=>'fa-envelope-open', 'color'=>'#f59e0b','bg'=>'#fffbeb'],
        ['label'=>'Total Sakit', 'val'=>$total_sakit, 'sub'=>'',                           'icon'=>'fa-notes-medical', 'color'=>'#06b6d4','bg'=>'#ecfeff'],
        ['label'=>'Total Alpha', 'val'=>$total_alpha, 'sub'=>'perlu perhatian',            'icon'=>'fa-user-times',    'color'=>'#ef4444','bg'=>'#fef2f2'],
    ];
    foreach ($stats as $st): ?>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:<?= $st['bg'] ?>;color:<?= $st['color'] ?>">
                <i class="fas <?= $st['icon'] ?>"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($st['val']) ?></div>
                <div class="stat-label"><?= $st['label'] ?>
                    <?php if ($st['sub']): ?><br><small><?= $st['sub'] ?></small><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <!-- Tabel Rekap -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-table text-primary"></i>
                    <span>Rekap Absensi</span>
                    <span class="badge" style="background:var(--primary-light);color:var(--primary);font-size:11px;padding:4px 10px;border-radius:20px">
                        <?= periode_label($tanggal_mulai, $tanggal_selesai) ?>
                    </span>
                </div>
                <span class="text-muted small"><?= count($rekap) ?> siswa</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle" style="font-size:13px">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th class="text-center" style="color:#22c55e">H</th>
                                <th class="text-center" style="color:#f59e0b">I</th>
                                <th class="text-center" style="color:#06b6d4">S</th>
                                <th class="text-center" style="color:#ef4444">A</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rekap as $r): ?>
                            <tr <?= $r['alpha'] >= 3 ? 'class="table-danger"' : '' ?>>
                                <td class="ps-4 text-muted" style="font-family:'DM Mono',monospace;font-size:11px">
                                    <?= htmlspecialchars($r['nis']) ?>
                                </td>
                                <td class="fw-medium"><?= htmlspecialchars($r['nama']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($r['nama_kelas']) ?></td>
                                <td class="text-center fw-semibold" style="color:#22c55e"><?= $r['hadir'] ?></td>
                                <td class="text-center fw-semibold" style="color:#f59e0b"><?= $r['izin'] ?></td>
                                <td class="text-center fw-semibold" style="color:#06b6d4"><?= $r['sakit'] ?></td>
                                <td class="text-center fw-semibold" style="color:#ef4444"><?= $r['alpha'] ?></td>
                                <td class="text-center text-muted"><?= $r['total'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar: top alpha + aksi -->
    <div class="col-lg-4 d-flex flex-column gap-4">

        <!-- Top Alpha -->
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-triangle text-danger"></i>
                <span>5 Alpha Terbanyak</span>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($top_alpha)): ?>
                <ol class="list-group list-group-flush list-group-numbered">
                    <?php foreach ($top_alpha as $siswa): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                        <div>
                            <div class="fw-semibold" style="font-size:13px">
                                <?= htmlspecialchars($siswa['nama']) ?>
                            </div>
                            <small class="text-muted"><?= htmlspecialchars($siswa['nama_kelas']) ?></small>
                        </div>
                        <span class="badge bg-danger rounded-pill"><?= $siswa['alpha'] ?>×</span>
                    </li>
                    <?php endforeach; ?>
                </ol>
                <?php else: ?>
                <div class="p-4 text-muted text-center" style="font-size:13px">Tidak ada siswa dengan alpha.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ekspor & Cetak -->
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="fas fa-download text-primary"></i>
                <span>Ekspor Data</span>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="export_excel_absensi.php?kelas_id=<?= $kelas_id ?>&tanggal_mulai=<?= urlencode($tanggal_mulai) ?>&tanggal_selesai=<?= urlencode($tanggal_selesai) ?>"
                   class="btn btn-outline-success w-100">
                    <i class="fas fa-file-excel me-2"></i> Ekspor Excel
                </a>
                <button onclick="window.print()" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-print me-2"></i> Cetak
                </button>
            </div>
        </div>

    </div>
</div>
<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>