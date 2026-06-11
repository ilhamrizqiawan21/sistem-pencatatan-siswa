<?php
$pageTitle = 'Pelanggaran Siswa';
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../includes/header.php';

$tahun_aktif = getTahunAjaranAktif($pdo);
$ta_id = $tahun_aktif['id'] ?? 0;

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show">' . htmlspecialchars($_SESSION['error']) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show">' . htmlspecialchars($_SESSION['success']) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    unset($_SESSION['success']);
}

// Data siswa untuk autocomplete
$siswa_datalist = $pdo->query("SELECT id, nis, nama FROM siswa WHERE status = 1 ORDER BY nama")->fetchAll();
$jenis_pelanggaran = $pdo->query("SELECT * FROM jenis_pelanggaran ORDER BY nama")->fetchAll();

// Filter
$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$siswa_id = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;
$bulan_mulai = isset($_GET['bulan_mulai']) ? (int)$_GET['bulan_mulai'] : 0;
$tahun_mulai = isset($_GET['tahun_mulai']) ? (int)$_GET['tahun_mulai'] : 0;
$bulan_selesai = isset($_GET['bulan_selesai']) ? (int)$_GET['bulan_selesai'] : 0;
$tahun_selesai = isset($_GET['tahun_selesai']) ? (int)$_GET['tahun_selesai'] : 0;

$kelas_list = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas")->fetchAll();

// Siswa untuk filter dropdown
$siswa_list = [];
if ($kelas_id > 0) {
    $stmt = $pdo->prepare("SELECT id, nis, nama FROM siswa WHERE kelas_id = ? AND status = 1 ORDER BY nama");
    $stmt->execute([$kelas_id]);
    $siswa_list = $stmt->fetchAll();
} else {
    $siswa_list = $pdo->query("SELECT id, nis, nama FROM siswa WHERE status = 1 ORDER BY nama")->fetchAll();
}

// Kondisi rentang tanggal
$where_date = '';
$params = [];
if ($tahun_mulai && $bulan_mulai && $tahun_selesai && $bulan_selesai) {
    $start = sprintf("%04d-%02d-01", $tahun_mulai, $bulan_mulai);
    $end = date("Y-m-t", strtotime(sprintf("%04d-%02d-01", $tahun_selesai, $bulan_selesai)));
    $where_date = " AND p.tanggal BETWEEN ? AND ?";
    $params[] = $start;
    $params[] = $end;
} elseif ($tahun_mulai && $bulan_mulai) {
    $start = sprintf("%04d-%02d-01", $tahun_mulai, $bulan_mulai);
    $where_date = " AND p.tanggal >= ?";
    $params[] = $start;
} elseif ($tahun_selesai && $bulan_selesai) {
    $end = date("Y-m-t", strtotime(sprintf("%04d-%02d-01", $tahun_selesai, $bulan_selesai)));
    $where_date = " AND p.tanggal <= ?";
    $params[] = $end;
}

$sql = "SELECT p.*, s.nis, s.nama, s.kelas_id, jp.nama as jenis_nama, jp.poin 
        FROM pelanggaran p 
        JOIN siswa s ON p.siswa_id = s.id 
        JOIN jenis_pelanggaran jp ON p.jenis_pelanggaran_id = jp.id 
        WHERE 1=1 $where_date";
if ($kelas_id > 0) {
    $sql .= " AND s.kelas_id = ?";
    $params[] = $kelas_id;
}
if ($siswa_id > 0) {
    $sql .= " AND s.id = ?";
    $params[] = $siswa_id;
}
$sql .= " ORDER BY p.tanggal DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pelanggaran = $stmt->fetchAll();

$thn_sekarang = date('Y');
?>
<style>
/* Custom dropdown autocomplete */
.siswa-autocomplete {
    position: relative;
}
.siswa-autocomplete input {
    width: 100%;
}
.siswa-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    max-height: 250px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    display: none;
}
.siswa-dropdown .siswa-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.9rem;
}
.siswa-dropdown .siswa-item:hover {
    background-color: #f0f7ff;
}
.siswa-dropdown .siswa-item strong {
    font-weight: 600;
}
</style>

<div class="card mb-3">
    <div class="card-header">Tambah Pelanggaran</div>
    <div class="card-body">
        <form method="POST" action="proses.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Siswa</label>
                    <div class="siswa-autocomplete">
                        <input type="text" id="siswa_search" class="form-control" placeholder="Ketik NIS atau Nama" autocomplete="off" required>
                        <div id="siswa_dropdown" class="siswa-dropdown"></div>
                    </div>
                    <input type="hidden" name="siswa_id" id="siswa_id">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jenis Pelanggaran</label>
                    <select name="jenis_pelanggaran_id" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach ($jenis_pelanggaran as $jp): ?>
                            <option value="<?= $jp['id'] ?>"><?= htmlspecialchars($jp['nama']) ?> (Poin <?= $jp['poin'] ?>)</option>
                        <?php endforeach; ?>
                        <option value="lainnya">-- Lainnya (Tulis sendiri) --</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Keterangan (opsional)</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Catatan">
                </div>
            </div>
            <div id="fieldLainnya" style="display:none; margin-top:10px;" class="row">
                <div class="col-md-4">
                    <input type="text" name="nama_pelanggaran_baru" id="nama_pelanggaran_baru" class="form-control" placeholder="Nama pelanggaran baru">
                </div>
                <div class="col-md-2">
                    <input type="number" name="poin_baru" id="poin_baru" class="form-control" placeholder="Poin" value="5">
                </div>
            </div>
            <div class="mt-3">
                <input type="hidden" name="tahun_ajaran_id" value="<?= $ta_id ?>">
                <button type="submit" name="simpan" class="btn btn-primary">Simpan Pelanggaran</button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-info text-white">Filter Data Pelanggaran</div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label>Kelas</label>
                <select name="kelas_id" id="filter_kelas" class="form-select">
                    <option value="0">-- Semua Kelas --</option>
                    <?php foreach ($kelas_list as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $kelas_id == $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Siswa</label>
                <select name="siswa_id" id="filter_siswa" class="form-select">
                    <option value="0">-- Semua Siswa --</option>
                    <?php foreach ($siswa_list as $ss): ?>
                        <option value="<?= $ss['id'] ?>" <?= $siswa_id == $ss['id'] ? 'selected' : '' ?>><?= $ss['nis'] ?> - <?= htmlspecialchars($ss['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Bulan Mulai</label>
                <select name="bulan_mulai" class="form-select">
                    <option value="0">-- Pilih --</option>
                    <?php for ($m=1;$m<=12;$m++): ?>
                        <option value="<?= $m ?>" <?= ($bulan_mulai == $m) ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Tahun Mulai</label>
                <select name="tahun_mulai" class="form-select">
                    <option value="0">-- Pilih --</option>
                    <?php for ($y=$thn_sekarang-2;$y<=$thn_sekarang+1;$y++): ?>
                        <option value="<?= $y ?>" <?= ($tahun_mulai == $y) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Bulan Selesai</label>
                <select name="bulan_selesai" class="form-select">
                    <option value="0">-- Pilih --</option>
                    <?php for ($m=1;$m<=12;$m++): ?>
                        <option value="<?= $m ?>" <?= ($bulan_selesai == $m) ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Tahun Selesai</label>
                <select name="tahun_selesai" class="form-select">
                    <option value="0">-- Pilih --</option>
                    <?php for ($y=$thn_sekarang-2;$y<=$thn_sekarang+1;$y++): ?>
                        <option value="<?= $y ?>" <?= ($tahun_selesai == $y) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="index.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
        <div class="form-text text-muted mt-2">
            <i class="fas fa-info-circle"></i> Kosongkan filter untuk menampilkan semua data.
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Daftar Pelanggaran</div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr><th>NIS</th><th>Siswa</th><th>Kelas</th><th>Pelanggaran</th><th>Poin</th><th>Tanggal</th><th>Keterangan</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (count($pelanggaran) > 0): ?>
                    <?php foreach ($pelanggaran as $p): ?>
                    <tr>
                        <td><?= $p['nis'] ?></td>
                        <td><?= htmlspecialchars($p['nama']) ?></td>
                        <td><?= htmlspecialchars($p['nama_kelas'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($p['jenis_nama']) ?></td>
                        <td><?= $p['poin'] ?></td>
                        <td><?= date('d-m-Y', strtotime($p['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($p['keterangan']) ?></td>
                        <td><a href="proses.php?action=hapus&id=<?= $p['id'] ?>" onclick="return confirmDelete(this.href)" class="btn btn-danger btn-sm">Hapus</a></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center">Tidak ada data pelanggaran untuk filter yang dipilih.<?php if ($kelas_id > 0) echo ' Kelas ini kosong.'; ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Data siswa untuk autocomplete
const siswaData = <?php 
    $siswa_json = [];
    foreach ($siswa_datalist as $s) {
        $siswa_json[] = ['id' => $s['id'], 'nis' => $s['nis'], 'nama' => $s['nama']];
    }
    echo json_encode($siswa_json);
?>;

// Elemen autocomplete
const siswaSearchInput = document.getElementById('siswa_search');
const siswaDropdown = document.getElementById('siswa_dropdown');
const siswaIdHidden = document.getElementById('siswa_id');

function escapeHtml(str) {
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function renderSiswaDropdown(filter = '') {
    const filterLower = filter.toLowerCase();
    const filtered = siswaData.filter(s => 
        s.nis.toLowerCase().includes(filterLower) || 
        s.nama.toLowerCase().includes(filterLower)
    );
    if (filtered.length === 0 || filter === '') {
        siswaDropdown.style.display = 'none';
        return;
    }
    siswaDropdown.innerHTML = '';
    filtered.forEach(s => {
        const div = document.createElement('div');
        div.className = 'siswa-item';
        div.innerHTML = `<strong>${escapeHtml(s.nis)}</strong> - ${escapeHtml(s.nama)}`;
        div.dataset.id = s.id;
        div.addEventListener('click', () => {
            siswaSearchInput.value = `${s.nis} - ${s.nama}`;
            siswaIdHidden.value = s.id;
            siswaDropdown.style.display = 'none';
        });
        siswaDropdown.appendChild(div);
    });
    siswaDropdown.style.display = 'block';
}

siswaSearchInput.addEventListener('input', function() {
    renderSiswaDropdown(this.value);
});
siswaSearchInput.addEventListener('focus', function() {
    renderSiswaDropdown(this.value);
});
document.addEventListener('click', function(e) {
    if (!siswaSearchInput.contains(e.target) && !siswaDropdown.contains(e.target)) {
        siswaDropdown.style.display = 'none';
    }
});

// Toggle field "Lainnya"
const selectJenis = document.querySelector('select[name="jenis_pelanggaran_id"]');
const fieldLainnya = document.getElementById('fieldLainnya');
if (selectJenis) {
    selectJenis.addEventListener('change', function() {
        fieldLainnya.style.display = this.value === 'lainnya' ? 'flex' : 'none';
    });
}

// Filter siswa berdasarkan kelas (AJAX)
const kelasFilter = document.getElementById('filter_kelas');
const siswaFilter = document.getElementById('filter_siswa');
if (kelasFilter && siswaFilter) {
    kelasFilter.addEventListener('change', function() {
        const kelasId = this.value;
        fetch('get_siswa_by_kelas.php?kelas_id=' + kelasId)
            .then(response => response.json())
            .then(data => {
                siswaFilter.innerHTML = '<option value="0">-- Semua Siswa --</option>';
                data.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.nis + ' - ' + s.nama;
                    siswaFilter.appendChild(opt);
                });
            });
    });
    if (kelasFilter.value != 0) {
        kelasFilter.dispatchEvent(new Event('change'));
    }
}
</script>
<?php require_once '../../includes/footer.php'; ?>