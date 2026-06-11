<?php
$pageTitle = 'Surat Izin Siswa';
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../includes/header.php';

if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show">' . htmlspecialchars($_SESSION['success']) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show">' . htmlspecialchars($_SESSION['error']) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    unset($_SESSION['error']);
}

$tahun_aktif = getTahunAjaranAktif($pdo);
$ta_id = $tahun_aktif['id'] ?? 0;

// Data siswa untuk autocomplete
$siswa_datalist = $pdo->query("SELECT s.id, s.nis, s.nama, k.nama_kelas 
                               FROM siswa s 
                               JOIN kelas k ON s.kelas_id = k.id 
                               WHERE s.status = 1 
                               ORDER BY s.nama")->fetchAll();

// Filter
$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$tanggal_filter = isset($_GET['tanggal_filter']) ? $_GET['tanggal_filter'] : '';
$jenis_filter = isset($_GET['jenis_filter']) ? $_GET['jenis_filter'] : '';

$sql = "SELECT i.*, s.nis, s.nama, k.nama_kelas 
        FROM surat_izin i
        JOIN siswa s ON i.siswa_id = s.id
        JOIN kelas k ON s.kelas_id = k.id
        WHERE 1=1";
$params = [];
if ($kelas_id > 0) {
    $sql .= " AND s.kelas_id = ?";
    $params[] = $kelas_id;
}
if ($tanggal_filter) {
    $sql .= " AND i.tanggal = ?";
    $params[] = $tanggal_filter;
}
if ($jenis_filter) {
    $sql .= " AND i.jenis_izin = ?";
    $params[] = $jenis_filter;
}
$sql .= " ORDER BY i.tanggal DESC, i.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$izin_list = $stmt->fetchAll();

$kelas_list = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas")->fetchAll();
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
    <div class="card-header bg-primary text-white">Tambah Surat Izin</div>
    <div class="card-body">
        <form method="POST" action="proses.php" id="formIzin">
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
                    <label class="form-label">Jenis Izin</label>
                    <select name="jenis_izin" id="jenisIzin" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <option value="pulang">Izin Pulang</option>
                        <option value="biasa">Izin Biasa</option>
                    </select>
                </div>
                <div id="fieldPulang" style="display: none;" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Jam Pulang</label>
                        <input type="time" name="jam_berangkat" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Alasan Pulang</label>
                        <select name="alasan_pulang" class="form-select">
                            <option value="sakit">Sakit</option>
                            <option value="keluarga">Kepentingan Keluarga</option>
                            <option value="lomba">Lomba</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>
                <div id="fieldBiasa" style="display: none;" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Alasan Izin</label>
                        <input type="text" name="alasan_biasa" class="form-control" placeholder="Tulis alasan izin...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Keterangan (opsional)</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Catatan tambahan">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" name="simpan" class="btn btn-success w-100"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </div>
            <input type="hidden" name="tahun_ajaran_id" value="<?= $ta_id ?>">
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-info text-white">Filter & Daftar Surat Izin</div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label>Kelas</label>
                <select name="kelas_id" class="form-select">
                    <option value="0">Semua Kelas</option>
                    <?php foreach ($kelas_list as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $kelas_id == $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Tanggal</label>
                <input type="date" name="tanggal_filter" class="form-control" value="<?= $tanggal_filter ?>">
            </div>
            <div class="col-md-2">
                <label>Jenis Izin</label>
                <select name="jenis_filter" class="form-select">
                    <option value="">Semua</option>
                    <option value="pulang" <?= $jenis_filter == 'pulang' ? 'selected' : '' ?>>Izin Pulang</option>
                    <option value="biasa" <?= $jenis_filter == 'biasa' ? 'selected' : '' ?>>Izin Biasa</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="index.php" class="btn btn-secondary ms-2">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr><th>No</th><th>Tanggal</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Jenis Izin</th><th>Detail Alasan</th><th>Keterangan</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if (count($izin_list) > 0): ?>
                        <?php $no = 1; foreach ($izin_list as $i): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d-m-Y', strtotime($i['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($i['nis']) ?></td>
                            <td><?= htmlspecialchars($i['nama']) ?></td>
                            <td><?= htmlspecialchars($i['nama_kelas']) ?></td>
                            <td><?= $i['jenis_izin'] == 'pulang' ? 'Izin Pulang' : 'Izin Biasa' ?></td>
                            <td>
                                <?php if ($i['jenis_izin'] == 'pulang'): ?>
                                    Jam: <?= $i['jam_berangkat'] ?><br>
                                    Alasan: <?= ucfirst($i['alasan_pulang']) ?>
                                <?php else: ?>
                                    <?= nl2br(htmlspecialchars($i['alasan_biasa'])) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= nl2br(htmlspecialchars($i['keterangan'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning btn-edit" 
                                    data-id="<?= $i['id'] ?>"
                                    data-siswa_id="<?= $i['siswa_id'] ?>"
                                    data-siswa_text="<?= $i['nis'] . ' - ' . $i['nama'] . ' (' . $i['nama_kelas'] . ')' ?>"
                                    data-tanggal="<?= $i['tanggal'] ?>"
                                    data-jenis="<?= $i['jenis_izin'] ?>"
                                    data-jam="<?= $i['jam_berangkat'] ?>"
                                    data-alasan_pulang="<?= $i['alasan_pulang'] ?>"
                                    data-alasan_biasa="<?= htmlspecialchars($i['alasan_biasa']) ?>"
                                    data-keterangan="<?= htmlspecialchars($i['keterangan']) ?>">Edit</button>
                                <a href="proses.php?action=hapus&id=<?= $i['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center">Belum ada data surat izin.<?php if ($kelas_id > 0) echo ' Kelas ini kosong.'; ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="proses.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Surat Izin</h5>
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
                        <label>Jenis Izin</label>
                        <select name="jenis_izin" id="edit_jenis" class="form-select" required>
                            <option value="pulang">Izin Pulang</option>
                            <option value="biasa">Izin Biasa</option>
                        </select>
                    </div>
                    <div id="editFieldPulang" style="display: none;">
                        <div class="mb-2"><label>Jam Berangkat</label><input type="time" name="jam_berangkat" id="edit_jam" class="form-control"></div>
                        <div class="mb-2"><label>Alasan Pulang</label><select name="alasan_pulang" id="edit_alasan_pulang" class="form-select"><option value="sakit">Sakit</option><option value="keluarga">Kepentingan Keluarga</option><option value="lomba">Lomba</option><option value="lainnya">Lainnya</option></select></div>
                    </div>
                    <div id="editFieldBiasa" style="display: none;">
                        <div class="mb-2"><label>Alasan Izin</label><input type="text" name="alasan_biasa" id="edit_alasan_biasa" class="form-control"></div>
                    </div>
                    <div class="mb-2"><label>Keterangan</label><input type="text" name="keterangan" id="edit_keterangan" class="form-control"></div>
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

buildSiswaDropdown('siswa_search', 'siswa_dropdown', 'siswa_id');

// Toggle fields untuk jenis izin
const jenisSelect = document.getElementById('jenisIzin');
const fieldPulang = document.getElementById('fieldPulang');
const fieldBiasa = document.getElementById('fieldBiasa');
function toggleFields() {
    if (jenisSelect.value === 'pulang') {
        fieldPulang.style.display = 'flex';
        fieldBiasa.style.display = 'none';
        document.querySelector('select[name="alasan_pulang"]').required = true;
        document.querySelector('input[name="alasan_biasa"]').required = false;
    } else if (jenisSelect.value === 'biasa') {
        fieldPulang.style.display = 'none';
        fieldBiasa.style.display = 'flex';
        document.querySelector('select[name="alasan_pulang"]').required = false;
        document.querySelector('input[name="alasan_biasa"]').required = true;
    } else {
        fieldPulang.style.display = 'none';
        fieldBiasa.style.display = 'none';
    }
}
if (jenisSelect) {
    jenisSelect.addEventListener('change', toggleFields);
    toggleFields();
}

// Modal edit
const editBtns = document.querySelectorAll('.btn-edit');
const editJenis = document.getElementById('edit_jenis');
const editFieldPulang = document.getElementById('editFieldPulang');
const editFieldBiasa = document.getElementById('editFieldBiasa');
function toggleEditFields() {
    if (editJenis.value === 'pulang') {
        editFieldPulang.style.display = 'block';
        editFieldBiasa.style.display = 'none';
    } else {
        editFieldPulang.style.display = 'none';
        editFieldBiasa.style.display = 'block';
    }
}
if (editJenis) editJenis.addEventListener('change', toggleEditFields);
editBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit_id').value = this.dataset.id;
        document.getElementById('siswa_search_edit').value = this.dataset.siswa_text;
        document.getElementById('siswa_id_edit').value = this.dataset.siswa_id;
        document.getElementById('edit_tanggal').value = this.dataset.tanggal;
        document.getElementById('edit_jenis').value = this.dataset.jenis;
        document.getElementById('edit_jam').value = this.dataset.jam || '';
        document.getElementById('edit_alasan_pulang').value = this.dataset.alasan_pulang || 'sakit';
        document.getElementById('edit_alasan_biasa').value = this.dataset.alasan_biasa || '';
        document.getElementById('edit_keterangan').value = this.dataset.keterangan || '';
        toggleEditFields();
        new bootstrap.Modal(document.getElementById('editModal')).show();
        buildSiswaDropdown('siswa_search_edit', 'siswa_dropdown_edit', 'siswa_id_edit');
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>