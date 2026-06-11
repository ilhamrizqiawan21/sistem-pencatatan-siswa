<?php
$pageTitle = 'Absensi Harian';
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../includes/header.php';

// ── Validasi & sanitasi input ─────────────────────────────────
$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$tanggal  = isset($_GET['tanggal']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tanggal'])
            ? $_GET['tanggal'] : date('Y-m-d');

$tahun_aktif = getTahunAjaranAktif($pdo);
$ta_id       = $tahun_aktif['id'] ?? 0;

$kelas_list  = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas")->fetchAll();

$siswa_data  = [];
$absensi_map = [];
$ringkasan   = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];

if ($kelas_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM siswa WHERE kelas_id = ? AND status = 1 ORDER BY nama");
    $stmt->execute([$kelas_id]);
    $siswa_data = $stmt->fetchAll();

    // Map absensi yang sudah ada
    if ($ta_id > 0) {
        $stmt = $pdo->prepare(
            "SELECT siswa_id, status, keterangan
             FROM absensi
             WHERE tanggal = ? AND tahun_ajaran_id = ?"
        );
        $stmt->execute([$tanggal, $ta_id]);
        foreach ($stmt->fetchAll() as $row) {
            $absensi_map[$row['siswa_id']] = $row;
        }

        // Ringkasan hanya untuk kelas terpilih
        $stmt = $pdo->prepare(
            "SELECT a.status, COUNT(*) AS total
             FROM absensi a
             JOIN siswa s ON a.siswa_id = s.id
             WHERE a.tanggal = ? AND a.tahun_ajaran_id = ? AND s.kelas_id = ?
             GROUP BY a.status"
        );
        $stmt->execute([$tanggal, $ta_id, $kelas_id]);
        foreach ($stmt->fetchAll() as $row) {
            $ringkasan[$row['status']] = (int)$row['total'];
        }
    }
}

// ── Flash message ─────────────────────────────────────────────
$flash = $_SESSION['success'] ?? null;
unset($_SESSION['success']);
$flash_error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>

<?php if ($flash): ?>
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert" data-autohide>
    <i class="fas fa-check-circle"></i>
    <?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($flash_error): ?>
<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert" data-autohide>
    <i class="fas fa-exclamation-circle"></i>
    <?= htmlspecialchars($flash_error, ENT_QUOTES, 'UTF-8') ?>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-2 text-primary"></i>
        <span>Filter Absensi</span>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Kelas</label>
                <select name="kelas_id" class="form-select" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($kelas_list as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $kelas_id == $k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control"
                       value="<?= htmlspecialchars($tanggal, ENT_QUOTES, 'UTF-8') ?>"
                       max="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Tampilkan
                </button>
            </div>
            <?php if ($kelas_id > 0): ?>
            <div class="col-md-2">
                <a href="index.php?kelas_id=<?= $kelas_id ?>&tanggal=<?= date('Y-m-d') ?>"
                   class="btn btn-outline-secondary w-100">
                    <i class="fas fa-today me-1"></i> Hari Ini
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if (!$ta_id): ?>
<div class="alert alert-warning d-flex align-items-center gap-2">
    <i class="fas fa-exclamation-triangle"></i>
    <div>Tahun ajaran aktif belum diatur.
        <a href="<?= BASE_URL ?>modules/pengaturan/" class="alert-link">Atur sekarang</a>
    </div>
</div>
<?php elseif ($kelas_id > 0 && empty($siswa_data)): ?>
<div class="alert alert-warning d-flex align-items-center gap-2">
    <i class="fas fa-users-slash"></i>
    Belum ada siswa aktif di kelas ini.
</div>
<?php elseif ($kelas_id > 0 && !empty($siswa_data)): ?>

<!-- Ringkasan -->
<div class="row g-3 mb-4">
    <?php
    $stat_config = [
        'H' => ['label' => 'Hadir',  'icon' => 'fa-check-circle',    'color' => '#22c55e', 'bg' => '#f0fdf4'],
        'I' => ['label' => 'Izin',   'icon' => 'fa-envelope-open',   'color' => '#f59e0b', 'bg' => '#fffbeb'],
        'S' => ['label' => 'Sakit',  'icon' => 'fa-notes-medical',   'color' => '#06b6d4', 'bg' => '#ecfeff'],
        'A' => ['label' => 'Alpha',  'icon' => 'fa-user-times',      'color' => '#ef4444', 'bg' => '#fef2f2'],
    ];
    foreach ($stat_config as $key => $cfg): ?>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['color'] ?>">
                <i class="fas <?= $cfg['icon'] ?>"></i>
            </div>
            <div>
                <div class="stat-value"><?= $ringkasan[$key] ?></div>
                <div class="stat-label"><?= $cfg['label'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Form Absensi -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-calendar-check text-primary"></i>
            <span>Form Absensi —
                <span class="text-primary fw-semibold">
                    <?php
                    $kelas_nama = '';
                    foreach ($kelas_list as $k) {
                        if ($k['id'] == $kelas_id) { $kelas_nama = $k['nama_kelas']; break; }
                    }
                    echo htmlspecialchars($kelas_nama, ENT_QUOTES, 'UTF-8');
                    ?>
                </span>
            </span>
        </div>
        <span class="badge" style="background:var(--primary-light);color:var(--primary);font-size:12px;padding:6px 12px;border-radius:20px">
            <?= date('d F Y', strtotime($tanggal)) ?>
        </span>
    </div>
    <div class="card-body p-0">
        <form method="POST" action="proses.php" id="formAbsensi">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
            <input type="hidden" name="kelas_id"      value="<?= $kelas_id ?>">
            <input type="hidden" name="tanggal"        value="<?= htmlspecialchars($tanggal, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="tahun_ajaran_id" value="<?= $ta_id ?>">

            <!-- Toolbar: set semua -->
            <div class="px-4 py-3 border-bottom d-flex align-items-center gap-2 flex-wrap">
                <span class="text-muted small me-1">Set semua:</span>
                <?php foreach (['H' => 'Hadir', 'I' => 'Izin', 'S' => 'Sakit', 'A' => 'Alpha'] as $val => $lbl): ?>
                <button type="button" class="btn btn-sm btn-outline-secondary set-all-btn"
                        data-value="<?= $val ?>">
                    <?= $lbl ?>
                </button>
                <?php endforeach; ?>
                <span class="ms-auto text-muted small"><?= count($siswa_data) ?> siswa</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="ps-4">No</th>
                            <th width="14%">NIS</th>
                            <th>Nama Siswa</th>
                            <th width="20%">Status</th>
                            <th width="24%">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($siswa_data as $s):
                            $absen = $absensi_map[$s['id']] ?? ['status' => 'H', 'keterangan' => ''];
                        ?>
                        <tr>
                            <td class="ps-4 text-muted"><?= $no++ ?></td>
                            <td class="text-muted" style="font-family:'DM Mono',monospace;font-size:12px">
                                <?= htmlspecialchars($s['nis'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="fw-medium">
                                <?= htmlspecialchars($s['nama'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <input type="hidden" name="siswa_id[]" value="<?= $s['id'] ?>">
                            <td>
                                <select name="status[]" class="form-select form-select-sm status-select">
                                    <option value="H" <?= $absen['status'] === 'H' ? 'selected' : '' ?>>✓ Hadir</option>
                                    <option value="I" <?= $absen['status'] === 'I' ? 'selected' : '' ?>>✉ Izin</option>
                                    <option value="S" <?= $absen['status'] === 'S' ? 'selected' : '' ?>>✚ Sakit</option>
                                    <option value="A" <?= $absen['status'] === 'A' ? 'selected' : '' ?>>✗ Alpha</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="keterangan[]" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($absen['keterangan'], ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="Opsional">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-top d-flex align-items-center gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Simpan Absensi
                </button>
                <a href="index.php?kelas_id=<?= $kelas_id ?>&tanggal=<?= $tanggal ?>"
                   class="btn btn-outline-secondary">
                    <i class="fas fa-undo me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Set semua status sekaligus
document.querySelectorAll('.set-all-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const val = btn.dataset.value;
        document.querySelectorAll('.status-select').forEach(sel => sel.value = val);
        // Update warna row
        colorRows();
    });
});

// Warna row sesuai status
function colorRows() {
    const colors = { H: '', I: '#fffbeb', S: '#ecfeff', A: '#fef2f2' };
    document.querySelectorAll('.status-select').forEach(sel => {
        const row = sel.closest('tr');
        row.style.background = colors[sel.value] || '';
    });
}

document.querySelectorAll('.status-select').forEach(sel => {
    sel.addEventListener('change', colorRows);
});

colorRows(); // init
</script>

<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>