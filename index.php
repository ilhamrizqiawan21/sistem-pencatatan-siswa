<?php
// ============================================
// MTS Al-Ihsan - Main Router & Entry Point
// ============================================

// Load environment variables FIRST
require_once 'config/env.php';

// Load error handler
require_once 'config/error-handler.php';

// Load configuration
require_once 'config/constants.php';
require_once 'config/db.php';
require_once 'config/functions.php';
require_once 'config/auth.php';
require_once 'config/router.php';

// Require login for most pages
if ($router->getPage() !== 'login' && $router->getPage() !== 'debug' && !isLoggedIn()) {
    redirectTo('login');
}

// Handle logout
if ($router->getPage() === 'logout') {
    logout();
    redirectTo('login');
}

// Handle module requests
if ($router->getModule()) {
    $moduleName = $router->getModule();
    $pageName = $router->getPage();
    
    // Try to load specific page file first (e.g., absensi_bulanan.php)
    if ($pageName !== 'index') {
        $modulePath = __DIR__ . '/modules/' . $moduleName . '/' . $pageName . '.php';
        if (file_exists($modulePath)) {
            chdir(dirname($modulePath));
            require_once $modulePath;
            exit;
        }
    }
    
    // Fallback to index.php
    $modulePath = __DIR__ . '/modules/' . $moduleName . '/index.php';
    if (file_exists($modulePath)) {
        chdir(dirname($modulePath));
        require_once $modulePath;
        exit;
    }
}

// Default: Dashboard
$pageTitle = 'Dashboard';
require_once 'includes/header.php';

// Ambil tahun ajaran aktif
$tahun_aktif = getTahunAjaranAktif($pdo);
$ta_id = $tahun_aktif['id'] ?? 0;

// Filter dari GET
$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = date('Y');

// ---- Statistik Utama ----
$total_siswa = $pdo->query("SELECT COUNT(*) FROM siswa WHERE status = 1")->fetchColumn();
$total_kelas = $pdo->query("SELECT COUNT(*) FROM kelas")->fetchColumn();

// Kehadiran hari ini
$today = date('Y-m-d');
$hadir_hari_ini = $pdo->prepare("SELECT COUNT(*) FROM absensi WHERE tanggal = ? AND status = 'H'");
$hadir_hari_ini->execute([$today]);
$hadir = $hadir_hari_ini->fetchColumn();
$total_absen_hari_ini = $pdo->prepare("SELECT COUNT(*) FROM absensi WHERE tanggal = ?");
$total_absen_hari_ini->execute([$today]);
$total_absen = $total_absen_hari_ini->fetchColumn();
$persen_hadir = ($total_absen > 0) ? round(($hadir / $total_absen) * 100) : 0;

$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = date('Y');

// ---- Statistik Utama ----
$total_siswa = $pdo->query("SELECT COUNT(*) FROM siswa WHERE status = 1")->fetchColumn();
$total_kelas = $pdo->query("SELECT COUNT(*) FROM kelas")->fetchColumn();

// Kehadiran hari ini
$today = date('Y-m-d');
$hadir_hari_ini = $pdo->prepare("SELECT COUNT(*) FROM absensi WHERE tanggal = ? AND status = 'H'");
$hadir_hari_ini->execute([$today]);
$hadir = $hadir_hari_ini->fetchColumn();
$total_absen_hari_ini = $pdo->prepare("SELECT COUNT(*) FROM absensi WHERE tanggal = ?");
$total_absen_hari_ini->execute([$today]);
$total_absen = $total_absen_hari_ini->fetchColumn();
$persen_hadir = ($total_absen > 0) ? round(($hadir / $total_absen) * 100) : 0;

// ---- 1. 5 Siswa dengan Pelanggaran Terbanyak (sepanjang data) ----
$sql_top_pelanggaran_siswa = "SELECT s.id, s.nis, s.nama, COUNT(p.id) as total_pelanggaran
                              FROM siswa s
                              LEFT JOIN pelanggaran p ON s.id = p.siswa_id
                              WHERE s.status = 1
                              GROUP BY s.id
                              ORDER BY total_pelanggaran DESC
                              LIMIT 5";
$top_pelanggaran_siswa = $pdo->query($sql_top_pelanggaran_siswa)->fetchAll();

// ---- 2. 5 Siswa dengan Ketidakhadiran Terbanyak (Sakit+Izin+Alpha) ----
$sql_top_ketidakhadiran = "SELECT s.id, s.nis, s.nama,
                          SUM(CASE WHEN a.status = 'S' THEN 1 ELSE 0 END) as sakit,
                          SUM(CASE WHEN a.status = 'I' THEN 1 ELSE 0 END) as izin,
                          SUM(CASE WHEN a.status = 'A' THEN 1 ELSE 0 END) as alpha,
                          (SUM(CASE WHEN a.status IN ('S','I','A') THEN 1 ELSE 0 END)) as total_absen
                          FROM siswa s
                          LEFT JOIN absensi a ON s.id = a.siswa_id
                          WHERE s.status = 1
                          GROUP BY s.id
                          ORDER BY total_absen DESC
                          LIMIT 5";
$top_ketidakhadiran = $pdo->query($sql_top_ketidakhadiran)->fetchAll();

// ---- 3. 10 Siswa dengan Keterlambatan Terbanyak (sepanjang data) ----
$sql_top_terlambat = "SELECT s.id, s.nis, s.nama, COUNT(kt.id) as total_terlambat
                      FROM siswa s
                      LEFT JOIN keterlambatan kt ON s.id = kt.siswa_id
                      WHERE s.status = 1
                      GROUP BY s.id
                      ORDER BY total_terlambat DESC
                      LIMIT 10";
$top_terlambat = $pdo->query($sql_top_terlambat)->fetchAll();

// ---- 4. Peringkat Kebersihan Kelas (Top 5) ----
$sql_kebersihan = "SELECT k.nama_kelas, AVG(kb.nilai_total) as rata 
                   FROM kebersihan_kelas kb 
                   JOIN kelas k ON kb.kelas_id = k.id 
                   WHERE kb.tahun_ajaran_id = ? 
                   GROUP BY kb.kelas_id 
                   ORDER BY rata DESC LIMIT 5";
$stmt_keb = $pdo->prepare($sql_kebersihan);
$stmt_keb->execute([$ta_id]);
$top_kebersihan = $stmt_keb->fetchAll();

// ---- 5. 5 Siswa dengan Izin Terbanyak ----
$sql_top_izin = "SELECT s.id, s.nis, s.nama, COUNT(a.id) as total_izin
                 FROM siswa s
                 LEFT JOIN absensi a ON s.id = a.siswa_id AND a.status = 'I'
                 WHERE s.status = 1
                 GROUP BY s.id
                 ORDER BY total_izin DESC
                 LIMIT 5";
$top_izin = $pdo->query($sql_top_izin)->fetchAll();

// ---- 6. Distribusi Kehadiran Bulan Ini (Donut) ----
$sql_kehadiran = "SELECT status, COUNT(*) as total 
                  FROM absensi 
                  WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ? 
                  GROUP BY status";
$stmt_hadir = $pdo->prepare($sql_kehadiran);
$stmt_hadir->execute([$bulan, $tahun]);
$status_data = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];
while ($row = $stmt_hadir->fetch()) {
    $status_data[$row['status']] = $row['total'];
}
$labels_kehadiran = ['Hadir', 'Izin', 'Sakit', 'Alpha'];
$data_kehadiran = [$status_data['H'], $status_data['I'], $status_data['S'], $status_data['A']];

// ---- Prestasi Terbaru ----
$sql_prestasi = "SELECT p.*, s.nama, tk.nama as tingkat 
                 FROM prestasi p 
                 JOIN siswa s ON p.siswa_id = s.id 
                 JOIN tingkat_prestasi tk ON p.tingkat_prestasi_id = tk.id 
                 ORDER BY p.tanggal DESC LIMIT 3";
$prestasi_baru = $pdo->query($sql_prestasi)->fetchAll();

// ---- Daftar kelas untuk dropdown filter ----
$kelas_list = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas")->fetchAll();
?>

<style>
.card-stat {
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    border-radius: 1rem;
    background: white;
}
.card-stat:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
}
.icon-circle {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
.bg-soft-primary { background-color: rgba(59,130,246,0.1); }
.bg-soft-success { background-color: rgba(16,185,129,0.1); }
.bg-soft-warning { background-color: rgba(245,158,11,0.1); }
.bg-soft-danger { background-color: rgba(239,68,68,0.1); }
.text-primary { color: #3B82F6 !important; }
.text-success { color: #10B981 !important; }
.text-warning { color: #F59E0B !important; }
.text-danger { color: #EF4444 !important; }
.leaderboard-item {
    transition: background 0.2s;
    background: white;
    border-bottom-color: #E5E7EB;
}
.leaderboard-item:hover {
    background-color: #F3F4F6;
}
</style>

<div class="container-fluid px-4">
    <!-- Filter Bar -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold"><i class="fas fa-calendar-alt me-1"></i> Tahun Ajaran Aktif</label>
                    <input type="text" class="form-control bg-white" 
                           value="<?= htmlspecialchars($tahun_aktif['tahun'] ?? 'Belum diatur') . ' - Semester ' . ($tahun_aktif['semester'] ?? '-') ?>" 
                           readonly disabled>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold"><i class="fas fa-door-open me-1"></i> Filter Kelas</label>
                    <select name="kelas_id" class="form-select">
                        <option value="0">-- Semua Kelas --</option>
                        <?php foreach ($kelas_list as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= $kelas_id == $k['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold"><i class="fas fa-chart-line me-1"></i> Bulan</label>
                    <select name="bulan" class="form-select">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $bulan == $m ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Kartu Statistik (hanya 3 kartu) -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-stat shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Siswa</h6>
                            <h2 class="mb-0 fw-bold"><?= number_format($total_siswa) ?></h2>
                        </div>
                        <div class="icon-circle bg-soft-primary">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block"><i class="fas fa-user-check"></i> Aktif</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Kehadiran Hari Ini</h6>
                            <h2 class="mb-0 fw-bold"><?= $persen_hadir ?>%</h2>
                        </div>
                        <div class="icon-circle bg-soft-success">
                            <i class="fas fa-calendar-check fa-2x text-success"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">Hadir: <?= $hadir ?> / <?= $total_absen ?> siswa</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Kelas</h6>
                            <h2 class="mb-0 fw-bold"><?= $total_kelas ?></h2>
                        </div>
                        <div class="icon-circle bg-soft-warning">
                            <i class="fas fa-school fa-2x text-warning"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">Rombel aktif</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Baris 1: 5 Siswa Pelanggaran Terbanyak + Distribusi Kehadiran -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-0 pt-3">
                    <h5 class="mb-0"><i class="fas fa-gavel text-danger me-2"></i> 5 Siswa dengan Pelanggaran Terbanyak</h5>
                </div>
                <div class="card-body">
                    <?php if (count($top_pelanggaran_siswa) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php 
                            // Ganti medal dengan angka biasa
                            $no = 1;
                            foreach ($top_pelanggaran_siswa as $s):
                            ?>
                            <div class="list-group-item d-flex align-items-center gap-3 border-0 px-0 py-2">
                                <div class="fs-4" style="min-width: 40px; text-align: center;"><strong><?= $no ?></strong></div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= htmlspecialchars($s['nama']) ?></strong>
                                        <span class="badge bg-danger rounded-pill"><?= $s['total_pelanggaran'] ?>x</span>
                                    </div>
                                    <small class="text-muted">NIS: <?= htmlspecialchars($s['nis']) ?></small>
                                </div>
                            </div>
                            <?php $no++; endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">Belum ada data pelanggaran.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-0 pt-3">
                    <h5 class="mb-0"><i class="fas fa-chart-pie text-success me-2"></i> Distribusi Kehadiran (Bulan <?= date('F', mktime(0,0,0,$bulan,1)) ?>)</h5>
                </div>
                <div class="card-body">
                    <canvas id="kehadiranChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Baris 2: 5 Siswa dengan Ketidakhadiran Terbanyak -->
    <div class="row g-4 mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-0 pt-3">
                    <h5 class="mb-0"><i class="fas fa-user-slash text-warning me-2"></i> 5 Siswa dengan Ketidakhadiran Terbanyak (Sakit + Izin + Alpha)</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr><th>No</th><th>NIS</th><th>Nama</th><th>Sakit</th><th>Izin</th><th>Alpha</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($top_ketidakhadiran as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nis']) ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= $row['sakit'] ?></td>
                                <td><?= $row['izin'] ?></td>
                                <td><?= $row['alpha'] ?></td>
                                <td><strong><?= $row['total_absen'] ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (count($top_ketidakhadiran) == 0): ?>
                            <tr><td colspan="7" class="text-center text-muted">Belum ada data ketidakhadiran.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Baris 3: 5 Siswa dengan Izin Terbanyak + Peringkat Kebersihan -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-0 pt-3">
                    <h5 class="mb-0"><i class="fas fa-file-alt text-info me-2"></i> 5 Siswa dengan Izin Terbanyak</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr><th>No</th><th>NIS</th><th>Nama</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($top_izin as $iz): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($iz['nis']) ?></td>
                                <td><?= htmlspecialchars($iz['nama']) ?></td>
                                <td><span class="badge bg-info"><?= $iz['total_izin'] ?>x</span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (count($top_izin) == 0): ?>
                            <tr><td colspan="4" class="text-center text-muted">Belum ada data izin.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-0 pt-3">
                    <h5 class="mb-0"><i class="fas fa-trophy text-warning me-2"></i> Peringkat Kebersihan Kelas</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php 
                        $medals_k = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];
                        $idx = 0;
                        if (!empty($top_kebersihan)):
                        foreach ($top_kebersihan as $tk): 
                        ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center leaderboard-item">
                            <span>
                                <span class="fs-5 me-2"><?= $medals_k[$idx] ?? '📋' ?></span>
                                <?= htmlspecialchars($tk['nama_kelas']) ?>
                            </span>
                            <span class="badge bg-primary rounded-pill"><?= round($tk['rata']) ?> poin</span>
                        </div>
                        <?php 
                        $idx++; 
                        endforeach; 
                        else: ?>
                        <div class="list-group-item text-center text-secondary">Belum ada data penilaian.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Baris 4: 10 Siswa dengan Keterlambatan Terbanyak -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header border-0 pt-3">
            <h5 class="mb-0"><i class="fas fa-clock text-warning me-2"></i> 10 Siswa dengan Keterlambatan Terbanyak</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr><th>No</th><th>NIS</th><th>Nama</th><th>Jumlah Terlambat</th></tr>
                </thead>
                <tbody>
                    <?php if (count($top_terlambat) > 0): ?>
                        <?php $no = 1; foreach ($top_terlambat as $t): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($t['nis']) ?></td>
                            <td><?= htmlspecialchars($t['nama']) ?></td>
                            <td><span class="badge bg-warning text-dark"><?= $t['total_terlambat'] ?>x</span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted">Belum ada data keterlambatan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Prestasi Terbaru (tetap) -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header border-0 pt-3">
            <h5 class="mb-0"><i class="fas fa-star text-warning me-2"></i> Prestasi Terbaru</h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <?php if (count($prestasi_baru) > 0): ?>
                    <?php foreach ($prestasi_baru as $prest): ?>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <?php if (!empty($prest['foto']) && file_exists(UPLOAD_PATH . 'prestasi/' . $prest['foto'])): ?>
                                <img src="<?= BASE_URL ?>uploads/prestasi/<?= $prest['foto'] ?>" class="card-img-top" style="height: 160px; object-fit: cover;" alt="Foto prestasi">
                            <?php else: ?>
                                <div class="bg-light text-center py-4"><i class="fas fa-award fa-4x text-secondary"></i></div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h6 class="card-title fw-bold"><?= htmlspecialchars($prest['nama']) ?></h6>
                                <p class="card-text small text-muted">
                                    <i class="fas fa-trophy text-warning"></i> <?= htmlspecialchars($prest['nama_prestasi']) ?><br>
                                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($prest['tingkat']) ?><br>
                                    <i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($prest['tanggal'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-secondary py-4">Belum ada prestasi yang dicatat.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctxHadir = document.getElementById('kehadiranChart')?.getContext('2d');
    if (ctxHadir) {
        new Chart(ctxHadir, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($labels_kehadiran) ?>,
                datasets: [{
                    data: <?= json_encode($data_kehadiran) ?>,
                    backgroundColor: ['#10B981', '#F59E0B', '#F97316', '#EF4444'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>