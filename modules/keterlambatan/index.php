<?php
$pageTitle = 'Keterlambatan Siswa';
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

// Filter
$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$siswa_id = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;
$tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : '';
$tanggal_selesai = isset($_GET['tanggal_selesai']) ? $_GET['tanggal_selesai'] : '';

$kelas_list = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas")->fetchAll();

$siswa_list = [];
if ($kelas_id > 0) {
    $stmt = $pdo->prepare("SELECT id, nis, nama FROM siswa WHERE kelas_id = ? AND status = 1 ORDER BY nama");
    $stmt->execute([$kelas_id]);
    $siswa_list = $stmt->fetchAll();
} else {
    $siswa_list = $pdo->query("SELECT id, nis, nama FROM siswa WHERE status = 1 ORDER BY nama")->fetchAll();
}

$date_condition = '';
if (!empty($tanggal_mulai) && !empty($tanggal_selesai)) {
    $date_condition = " AND kt.tanggal BETWEEN '$tanggal_mulai' AND '$tanggal_selesai' ";
} elseif (!empty($tanggal_mulai)) {
    $date_condition = " AND kt.tanggal >= '$tanggal_mulai' ";
} elseif (!empty($tanggal_selesai)) {
    $date_condition = " AND kt.tanggal <= '$tanggal_selesai' ";
}

$sql = "SELECT kt.*, s.nis, s.nama, s.kelas_id, kl.nama_kelas 
        FROM keterlambatan kt
        JOIN siswa s ON kt.siswa_id = s.id
        JOIN kelas kl ON s.kelas_id = kl.id
        WHERE 1=1 $date_condition";
if ($kelas_id > 0) $sql .= " AND s.kelas_id = $kelas_id";
if ($siswa_id > 0) $sql .= " AND s.id = $siswa_id";
$sql .= " ORDER BY kt.tanggal DESC, kt.jam_datang DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$keterlambatan_list = $stmt->fetchAll();
?>

<style>
.siswa-autocomplete { position: relative; }
.siswa-autocomplete input { width: 100%; }
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
.siswa-dropdown .siswa-item:hover { background-color: #f0f7ff; }
.siswa-dropdown .siswa-item strong { font-weight: 600; }
</style>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">Tambah Keterlambatan</div>
    <div class="card-body">
        <form method="POST" action="proses.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Siswa</label>
                    <div class="siswa-autocomplete">
                        <input type="text" id="siswa_search" class="form-control" placeholder="Ketik NIS atau Nama" autocomplete="off" required>
                        <div id="siswa_dropdown" class="siswa-dropdown"></div>
                    </div>
                    <input type="hidden" name="siswa_id" id="siswa_id">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Jam Datang</label>
                    <input type="time" name="jam_datang" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Alasan</label>
                    <input type="text" name="alasan" class="form-control" placeholder="Alasan keterlambatan" required>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" name="simpan" class="btn btn-success w-100"><i class="fas fa-save"></i></button>
                </div>
            </div>
            <input type="hidden" name="tahun_ajaran_id" value="<?= $ta_id ?>">
            <div class="mt-2 text-muted small">* pilih dengan NIS atau Nama.</div>
        </form>
    </div>
</div>
<div class="card mb-3">
    <div class="card-header bg-info text-white">Filter Data Keterlambatan</div>
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
            <div class="col-md-3">
                <label>Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control" value="<?= htmlspecialchars($tanggal_mulai) ?>">
            </div>
            <div class="col-md-3">
                <label>Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control" value="<?= htmlspecialchars($tanggal_selesai) ?>">
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
    <div class="card-header bg-info text-white">Daftar Keterlambatan</div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr><th>No</th><th>Tanggal</th><th>NIS</th><th>Nama Siswa</th><th>Kelas</th><th>Jam Datang</th><th>Alasan</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (count($keterlambatan_list) > 0): ?>
                    <?php $no = 1; foreach ($keterlambatan_list as $k): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= date('d-m-Y', strtotime($k['tanggal'])) ?></td>
                        <td><?= $k['nis'] ?></td>
                        <td><?= htmlspecialchars($k['nama']) ?></td>
                        <td><?= htmlspecialchars($k['nama_kelas']) ?></td>
                        <td><?= $k['jam_datang'] ?></td>
                        <td><?= htmlspecialchars($k['alasan']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning btn-edit" 
                                data-id="<?= $k['id'] ?>"
                                data-siswa_id="<?= $k['siswa_id'] ?>"
                                data-siswa_text="<?= $k['nis'] . ' - ' . $k['nama'] ?>"
                                data-tanggal="<?= $k['tanggal'] ?>"
                                data-jam="<?= $k['jam_datang'] ?>"
                                data-alasan="<?= htmlspecialchars($k['alasan']) ?>">Edit</button>
                            <a href="proses.php?action=hapus&id=<?= $k['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center">Tidak ada data keterlambatan untuk filter yang dipilih.<?= $kelas_id > 0 ? ' Kelas ini kosong.' : '' ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="proses.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Keterlambatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-2">
                        <label>Siswa</label>
                        <div class="siswa-autocomplete">
                            <input type="text" id="siswa_search_edit" class="form-control" placeholder="Ketik NIS atau Nama" autocomplete="off" required>
                            <div id="siswa_dropdown_edit" class="siswa-dropdown"></div>
                        </div>
                        <input type="hidden" name="siswa_id_edit" id="siswa_id_edit">
                    </div>
                    <div class="mb-2">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" id="edit_tanggal" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Jam Datang</label>
                        <input type="time" name="jam_datang" id="edit_jam" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Alasan</label>
                        <input type="text" name="alasan" id="edit_alasan" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Data siswa
const siswaData = <?php 
    $siswa_json = [];
    foreach ($siswa_datalist as $s) {
        $siswa_json[] = ['id' => $s['id'], 'nis' => $s['nis'], 'nama' => $s['nama']];
    }
    echo json_encode($siswa_json);
?>;

// Fungsi render dropdown
function buildSiswaDropdown(inputId, dropdownId, hiddenId) {
    const input = document.getElementById(inputId);
    const dropdown = document.getElementById(dropdownId);
    const hidden = document.getElementById(hiddenId);
    if (!input || !dropdown || !hidden) return;

    function render(filter = '') {
        const filterLower = filter.toLowerCase();
        const filtered = siswaData.filter(s => 
            s.nis.toLowerCase().includes(filterLower) || 
            s.nama.toLowerCase().includes(filterLower)
        );
        if (filtered.length === 0 || filter === '') {
            dropdown.style.display = 'none';
            return;
        }
        dropdown.innerHTML = '';
        filtered.forEach(s => {
            const div = document.createElement('div');
            div.className = 'siswa-item';
            div.innerHTML = `<strong>${escapeHtml(s.nis)}</strong> - ${escapeHtml(s.nama)}`;
            div.addEventListener('click', () => {
                input.value = `${s.nis} - ${s.nama}`;
                hidden.value = s.id;
                dropdown.style.display = 'none';
            });
            dropdown.appendChild(div);
        });
        dropdown.style.display = 'block';
    }

    function escapeHtml(str) {
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    input.addEventListener('input', () => render(input.value));
    input.addEventListener('focus', () => render(input.value));
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

// Inisialisasi autocomplete untuk form tambah
buildSiswaDropdown('siswa_search', 'siswa_dropdown', 'siswa_id');

// Inisialisasi untuk modal edit (nantinya akan diisi data siswa_text saat tombol edit diklik)
// Untuk edit, kita set nanti setelah data tombol edit diisi
const editBtns = document.querySelectorAll('.btn-edit');
editBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit_id').value = this.dataset.id;
        document.getElementById('siswa_search_edit').value = this.dataset.siswa_text;
        document.getElementById('siswa_id_edit').value = this.dataset.siswa_id;
        document.getElementById('edit_tanggal').value = this.dataset.tanggal;
        document.getElementById('edit_jam').value = this.dataset.jam;
        document.getElementById('edit_alasan').value = this.dataset.alasan;
        new bootstrap.Modal(document.getElementById('editModal')).show();
        // Re-inisialisasi dropdown untuk edit
        buildSiswaDropdown('siswa_search_edit', 'siswa_dropdown_edit', 'siswa_id_edit');
    });
});

// Filter siswa (AJAX) untuk dropdown filter
const kelasFilter = document.getElementById('filter_kelas');
const siswaFilter = document.getElementById('filter_siswa');
if (kelasFilter && siswaFilter) {
    kelasFilter.addEventListener('change', function() {
        fetch('get_siswa_by_kelas.php?kelas_id=' + this.value)
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
    if (kelasFilter.value != 0) kelasFilter.dispatchEvent(new Event('change'));
}
</script>
<?php require_once '../../includes/footer.php'; ?>