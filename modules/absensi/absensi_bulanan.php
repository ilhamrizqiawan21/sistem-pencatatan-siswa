<?php
$pageTitle = 'Absensi Bulanan';
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../includes/header.php';

// ── Helper ────────────────────────────────────────────────────
function nama_bulan(int $bulan): string {
    $list = [1=>'Januari','Februari','Maret','April','Mei','Juni',
             7=>'Juli','Agustus','September','Oktober','November','Desember'];
    return $list[$bulan] ?? '';
}

// ── Proses POST ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_absensi'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        setFlash('Request tidak valid', 'error');
        header('Location: absensi_bulanan.php');
        exit;
    }
    $kelas_id = (int)($_POST['kelas_id'] ?? 0);
    $bulan    = (int)($_POST['bulan']    ?? 0);
    $tahun    = (int)($_POST['tahun']    ?? 0);

    // Validasi dasar
    if ($kelas_id < 1 || $bulan < 1 || $bulan > 12 || $tahun < 2000) {
        setFlash('Data tidak valid. Silakan ulangi.', 'error');
        header("Location: absensi_bulanan.php");
        exit;
    }

    $tahun_ajaran = getTahunAjaranAktif($pdo);
    if (!$tahun_ajaran) {
        setFlash('Tahun ajaran aktif belum diatur. Silakan atur di menu Pengaturan.', 'error');
        header("Location: absensi_bulanan.php");
        exit;
    }
    $tahun_ajaran_id = (int)$tahun_ajaran['id'];

    $stmt = $pdo->prepare("SELECT id FROM siswa WHERE kelas_id = ? AND status = 1");
    $stmt->execute([$kelas_id]);
    $siswa_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $status_data     = $_POST['status']     ?? [];
    $keterangan_data = $_POST['keterangan'] ?? [];
    $jumlah_hari     = (int) date('t', mktime(0, 0, 0, $bulan, 1, $tahun));

    $inserted = 0;
    $updated  = 0;

    // Gunakan UPSERT (INSERT ... ON DUPLICATE KEY UPDATE) agar lebih efisien
    $upsert = $pdo->prepare(
        "INSERT INTO absensi (siswa_id, tanggal, status, keterangan, tahun_ajaran_id)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE status = VALUES(status), keterangan = VALUES(keterangan)"
    );

    $pdo->beginTransaction();
    try {
        foreach ($siswa_ids as $siswa_id) {
            $ket = htmlspecialchars(trim($keterangan_data[$siswa_id] ?? ''), ENT_QUOTES, 'UTF-8');
            for ($hari = 1; $hari <= $jumlah_hari; $hari++) {
                $tanggal = sprintf('%04d-%02d-%02d', $tahun, $bulan, $hari);
                $st = $status_data[$siswa_id][$tanggal] ?? '';
                if (!in_array($st, ['H','I','S','A'], true)) continue;
                $upsert->execute([$siswa_id, $tanggal, $st, $ket, $tahun_ajaran_id]);
                $upsert->rowCount() === 1 ? $inserted++ : $updated++;
            }
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('Gagal menyimpan absensi: ' . $e->getMessage(), 'error');
        header("Location: absensi_bulanan.php?kelas_id=$kelas_id&bulan=$bulan&tahun=$tahun");
        exit;
    }

    setFlash("Absensi berhasil disimpan. Baru: $inserted, Diperbarui: $updated.", 'success');
    header("Location: absensi_bulanan.php?kelas_id=$kelas_id&bulan=$bulan&tahun=$tahun");
    exit;
}

// ── GET: siapkan data tampilan ─────────────────────────────────
$kelas_list = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas")->fetchAll();
$kelas_id   = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$bulan      = isset($_GET['bulan'])    ? max(1, min(12, (int)$_GET['bulan']))    : (int)date('n');
$tahun      = isset($_GET['tahun'])    ? max(2000, (int)$_GET['tahun'])          : (int)date('Y');

$tahun_aktif     = getTahunAjaranAktif($pdo);
$tahun_ajaran_id = $tahun_aktif['id'] ?? 0;

$siswa_data  = [];
$absensi_map = []; // [siswa_id][tanggal] = status

if ($kelas_id > 0 && $tahun_ajaran_id > 0) {
    $stmt = $pdo->prepare("SELECT id, nis, nama FROM siswa WHERE kelas_id = ? AND status = 1 ORDER BY nama");
    $stmt->execute([$kelas_id]);
    $siswa_data = $stmt->fetchAll();

    // Ambil absensi bulan ini sekaligus
    $tgl_awal = sprintf('%04d-%02d-01', $tahun, $bulan);
    $tgl_akhir = sprintf('%04d-%02d-%02d', $tahun, $bulan, (int) date('t', mktime(0, 0, 0, $bulan, 1, $tahun)));
    $stmt = $pdo->prepare(
        "SELECT siswa_id, tanggal, status
         FROM absensi
         WHERE tanggal BETWEEN ? AND ? AND tahun_ajaran_id = ?"
    );
    $stmt->execute([$tgl_awal, $tgl_akhir, $tahun_ajaran_id]);
    foreach ($stmt->fetchAll() as $row) {
        $absensi_map[$row['siswa_id']][$row['tanggal']] = $row['status'];
    }
}

$jumlah_hari = $kelas_id > 0 ? (int) date('t', mktime(0, 0, 0, $bulan, 1, $tahun)) : 0;
$hari_list   = [];
for ($i = 1; $i <= $jumlah_hari; $i++) {
    $tgl = sprintf('%04d-%02d-%02d', $tahun, $bulan, $i);
    $hari_list[] = ['tgl' => $tgl, 'hari' => (int)date('N', strtotime($tgl)), 'd' => $i];
}
?>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-2 text-primary"></i> Filter Absensi Bulanan
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
                <label class="form-label">Bulan</label>
                <select name="bulan" class="form-select">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $bulan == $m ? 'selected' : '' ?>><?= nama_bulan($m) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tahun</label>
                <input type="number" name="tahun" class="form-control"
                       value="<?= $tahun ?>" min="2020" max="<?= date('Y') + 1 ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-table me-1"></i> Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (!$tahun_ajaran_id): ?>
<div class="alert alert-warning d-flex gap-2">
    <i class="fas fa-exclamation-triangle mt-1"></i>
    <div>Tahun ajaran aktif belum diatur.
        <a href="<?= BASE_URL ?>modules/pengaturan/" class="alert-link">Atur sekarang</a>
    </div>
</div>
<?php elseif ($kelas_id > 0 && empty($siswa_data)): ?>
<div class="alert alert-warning">Belum ada siswa aktif di kelas ini.</div>
<?php elseif ($kelas_id > 0 && !empty($siswa_data)): ?>

<!-- Grid Calendar View -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-calendar-grid-3 text-primary"></i>
            <span class="fw-semibold">
                <?php
                $kelas_nama = '';
                foreach ($kelas_list as $k) { if ($k['id'] == $kelas_id) { $kelas_nama = $k['nama_kelas']; break; } }
                echo htmlspecialchars($kelas_nama, ENT_QUOTES, 'UTF-8');
                ?>
                — <?= nama_bulan($bulan) ?> <?= $tahun ?>
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Legend -->
            <small class="text-muted">Status:</small>
            <?php foreach (['H'=>'Hadir','I'=>'Izin','S'=>'Sakit','A'=>'Alpha'] as $s=>$l): ?>
                <span class="badge" style="background:<?= ['H'=>'#22c55e','I'=>'#f59e0b','S'=>'#06b6d4','A'=>'#ef4444'][$s] ?>;font-size:10px"><?= $s ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" id="formBulanan">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
            <input type="hidden" name="simpan_absensi" value="1">
            <input type="hidden" name="kelas_id" value="<?= $kelas_id ?>">
            <input type="hidden" name="bulan"    value="<?= $bulan ?>">
            <input type="hidden" name="tahun"    value="<?= $tahun ?>">

            <!-- Attendance Table -->
            <div class="table-responsive table-responsive-x mb-4">
                <table class="attendance-table">
                    <thead>
                        <!-- Date Header Row -->
                        <tr class="header-dates">
                            <th class="th-nama">Nama Siswa</th>
                            <?php foreach ($hari_list as $h):
                                $hari_nama = ['','Sen','Sel','Rab','Kam','Jum','Sab','Ming'][$h['hari']];
                            ?>
                            <th class="th-date" data-date="<?= $h['tgl'] ?>">
                                <div><?= $h['d'] ?></div>
                                <small><?= $hari_nama ?></small>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                        <!-- Bulk Actions Row -->
                        <tr class="header-actions">
                            <th class="th-nama"></th>
                            <?php foreach ($hari_list as $h): ?>
                            <th class="th-actions" data-date="<?= $h['tgl'] ?>">
                                <select class="bulk-select" data-date="<?= $h['tgl'] ?>" aria-label="Bulk status <?= $h['d'] ?>">
                                    <option value="">-</option>
                                    <option value="H">H</option>
                                    <option value="I">I</option>
                                    <option value="S">S</option>
                                    <option value="A">A</option>
                                </select>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($siswa_data as $s): ?>
                        <tr class="student-row">
                            <td class="td-nama">
                                <div class="nama-wrapper">
                                    <div class="nama-text"><?= htmlspecialchars($s['nama'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="nis-text"><?= htmlspecialchars($s['nis'], ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                            </td>
                            <?php foreach ($hari_list as $h):
                                $st = $absensi_map[$s['id']][$h['tgl']] ?? '';
                                $colors = ['H'=>'#22c55e','I'=>'#f59e0b','S'=>'#06b6d4','A'=>'#ef4444'];
                                $bg = $st && isset($colors[$st]) ? $colors[$st] : 'transparent';
                            ?>
                            <td class="td-status">
                                <select name="status[<?= $s['id'] ?>][<?= $h['tgl'] ?>]"
                                        class="status-select"
                                        data-student="<?= $s['id'] ?>"
                                        data-date="<?= $h['tgl'] ?>"
                                        style="background:<?= $bg ?>">
                                    <option value="">-</option>
                                    <option value="H" <?= $st === 'H' ? 'selected' : '' ?>>H</option>
                                    <option value="I" <?= $st === 'I' ? 'selected' : '' ?>>I</option>
                                    <option value="S" <?= $st === 'S' ? 'selected' : '' ?>>S</option>
                                    <option value="A" <?= $st === 'A' ? 'selected' : '' ?>>A</option>
                                </select>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Footer Actions -->
            <div class="d-flex align-items-center gap-2 flex-wrap border-top pt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Simpan Absensi
                </button>
                <a href="absensi_bulanan.php?kelas_id=<?= $kelas_id ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>"
                   class="btn btn-outline-secondary">
                    <i class="fas fa-undo me-1"></i> Reset
                </a>
                <div class="ms-auto text-muted small">
                    Total Siswa: <strong><?= count($siswa_data) ?></strong>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
const statusColors = { '': 'transparent', H:'#22c55e', I:'#f59e0b', S:'#06b6d4', A:'#ef4444' };

// Update warna select saat change
document.querySelectorAll('.status-select').forEach(select => {
    const updateColor = () => {
        const status = select.value;
        select.style.background = statusColors[status] || 'transparent';
        select.style.color = status ? '#fff' : 'var(--text-body)';
    };
    updateColor();
    select.addEventListener('change', updateColor);
});

// Bulk action dropdowns
document.querySelectorAll('.bulk-select').forEach(select => {
    select.addEventListener('change', () => {
        const status = select.value;
        const date = select.dataset.date;
        document.querySelectorAll(`.status-select[data-date="${date}"]`).forEach(field => {
            field.value = status;
            field.style.background = statusColors[status] || 'transparent';
            field.style.color = status ? '#fff' : 'var(--text-body)';
        });
    });
});
</script>

<style>
.attendance-table {
    width: 100%;
    min-width: max-content;
    border-collapse: collapse;
    font-size: 13px;
}

.attendance-table th,
.attendance-table td {
    white-space: nowrap;
}

.table-responsive.table-responsive-x {
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
}

.attendance-table thead {
    background: var(--content-bg);
    position: sticky;
    top: 0;
    z-index: 10;
}

.attendance-table .header-dates th {
    background: linear-gradient(135deg, var(--primary-light) 0%, rgba(99,102,241,0.05) 100%);
    border: 1px solid var(--card-border);
    border-bottom: 2px solid var(--primary);
    padding: 10px 4px;
    text-align: center;
    font-weight: 600;
    color: var(--primary-dark);
    white-space: nowrap;
}

.attendance-table .header-dates .th-nama {
    background: linear-gradient(135deg, var(--primary-light) 0%, rgba(99,102,241,0.05) 100%);
    border: 1px solid var(--card-border);
    border-bottom: 2px solid var(--primary);
    padding: 10px;
    text-align: left;
    min-width: 150px;
    position: sticky;
    left: 0;
    z-index: 12;
}

.attendance-table .header-dates .th-date {
    min-width: 80px;
}

.attendance-table .header-dates .th-date div {
    font-size: 18px;
    font-weight: 700;
}

.attendance-table .header-dates .th-date small {
    display: block;
    font-size: 10px;
    color: var(--text-muted);
    text-transform: uppercase;
    margin-top: 2px;
}

.attendance-table .header-actions th {
    background: #f0f4f8;
    border: 1px solid var(--card-border);
    padding: 4px;
    text-align: center;
}

.attendance-table .header-actions .th-nama {
    background: #f0f4f8;
    border: 1px solid var(--card-border);
    position: sticky;
    left: 0;
    z-index: 12;
}

.attendance-table .header-actions .th-actions {
    min-width: 80px;
}

.bulk-select {
    width: 70px;
    padding: 6px 8px;
    border: 1px solid rgba(0,0,0,0.12);
    border-radius: 4px;
    background: #fff;
    color: var(--text-body);
    font-weight: 600;
    cursor: pointer;
}

.bulk-select:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(99,102,241,0.2);
}

.attendance-table tbody tr {
    border-bottom: 1px solid var(--card-border);
}

.attendance-table tbody tr:hover {
    background: rgba(99,102,241,0.03);
}

.attendance-table .student-row .td-nama {
    position: sticky;
    left: 0;
    z-index: 11;
    background: #fafbfc;
    border: 1px solid var(--card-border);
    padding: 8px 12px;
    font-weight: 500;
    min-width: 150px;
}

.attendance-table .student-row:hover .td-nama {
    background: rgba(99,102,241,0.08);
}

.nama-wrapper {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.nama-text {
    font-weight: 500;
    color: var(--text-body);
}

.nis-text {
    font-size: 11px;
    color: var(--text-muted);
}

.attendance-table .td-status {
    border: 1px solid var(--card-border);
    padding: 6px;
    text-align: center;
    min-width: 80px;
}

.status-select {
    width: 50px;
    height: 32px;
    padding: 4px;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 4px;
    font-weight: 600;
    cursor: pointer;
    font-size: 12px;
    color: var(--text-body);
    transition: all var(--transition);
    appearance: none;
    background-position: right 6px center;
    background-repeat: no-repeat;
    background-size: 16px;
    padding-right: 20px;
}

.status-select:hover {
    box-shadow: 0 2px 6px rgba(0,0,0,0.12);
}

.status-select:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(99,102,241,0.2);
}

@media (max-width: 1024px) {
    .attendance-table {
        font-size: 12px;
    }
    
    .attendance-table .header-dates .th-date {
        min-width: 70px;
    }
    
    .attendance-table .td-status {
        min-width: 70px;
        padding: 4px;
    }
    
    .status-select {
        width: 45px;
        height: 28px;
        font-size: 11px;
    }
}

@media (max-width: 768px) {
    .attendance-table {
        font-size: 11px;
    }
    
    .attendance-table .header-dates .th-date {
        min-width: 60px;
    }
    
    .attendance-table .td-status {
        min-width: 60px;
        padding: 3px;
    }
    
    .status-select {
        width: 40px;
        height: 26px;
        font-size: 10px;
        padding: 2px;
        padding-right: 16px;
    }
    
    .nama-text {
        font-size: 12px;
    }
    
    .nis-text {
        font-size: 10px;
    }
}
</style>

<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>