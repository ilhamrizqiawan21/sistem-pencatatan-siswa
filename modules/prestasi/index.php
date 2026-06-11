<?php
$pageTitle = 'Prestasi Siswa';
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../includes/header.php';

$tahun_aktif = getTahunAjaranAktif($pdo);
$ta_id = $tahun_aktif['id'] ?? 0;

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">'.htmlspecialchars($_SESSION['error']).'</div>';
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">'.htmlspecialchars($_SESSION['success']).'</div>';
    unset($_SESSION['success']);
}

$siswa_datalist = $pdo->query("SELECT id, nis, nama FROM siswa WHERE status = 1 ORDER BY nama")->fetchAll();
$tingkat_list = $pdo->query("SELECT * FROM tingkat_prestasi")->fetchAll();

// Query data prestasi
$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$sql = "SELECT p.*, s.nis, s.nama, k.nama_kelas, tp.nama as tingkat_nama 
        FROM prestasi p
        JOIN siswa s ON p.siswa_id = s.id
        JOIN kelas k ON s.kelas_id = k.id
        JOIN tingkat_prestasi tp ON p.tingkat_prestasi_id = tp.id
        WHERE 1=1";
$params = [];
if ($kelas_id > 0) {
    $sql .= " AND s.kelas_id = ?";
    $params[] = $kelas_id;
}
$sql .= " ORDER BY p.tanggal DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$prestasi = $stmt->fetchAll();

$kelas_list = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas")->fetchAll();
?>

<div class="card mb-3">
    <div class="card-header bg-primary text-white">Tambah Prestasi</div>
    <div class="card-body">
        <form method="POST" action="proses.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Siswa</label>
                    <input type="text" name="siswa_search" id="siswa_search" class="form-control" list="siswaList" placeholder="Ketik NIS atau Nama" autocomplete="off" required>
                    <datalist id="siswaList">
                        <?php foreach ($siswa_datalist as $s): ?>
                            <option value="<?= htmlspecialchars($s['nis'] . ' - ' . $s['nama']) ?>" data-id="<?= $s['id'] ?>">
                        <?php endforeach; ?>
                    </datalist>
                    <input type="hidden" name="siswa_id" id="siswa_id">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nama Prestasi</label>
                    <input type="text" name="nama_prestasi" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tingkat</label>
                    <select name="tingkat_prestasi_id" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach ($tingkat_list as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= $t['nama'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Juara</label>
                    <input type="text" name="juara" class="form-control" placeholder="1/2/3">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Penyelenggara</label>
                    <input type="text" name="penyelenggara" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Foto (opsional)</label>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" name="simpan" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </div>
            <input type="hidden" name="tahun_ajaran_id" value="<?= $ta_id ?>">
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-info text-white">Filter & Daftar Prestasi</div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <label>Kelas</label>
                <select name="kelas_id" class="form-select" onchange="this.form.submit()">
                    <option value="0">Semua Kelas</option>
                    <?php foreach ($kelas_list as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $kelas_id == $k['id'] ? 'selected' : '' ?>><?= $k['nama_kelas'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Prestasi</th><th>Tingkat</th><th>Juara</th><th>Tanggal</th><th>Foto</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($prestasi as $p): ?>
                    <tr>
                        <td><?= $p['nis'] ?></td>
                        <td><?= htmlspecialchars($p['nama']) ?></td>
                        <td><?= $p['nama_kelas'] ?></td>
                        <td><?= htmlspecialchars($p['nama_prestasi']) ?></td>
                        <td><?= $p['tingkat_nama'] ?></td>
                        <td><?= $p['juara'] ?></td>
                        <td><?= $p['tanggal'] ?></td>
                        <td><?php if ($p['foto']): ?><img src="<?= BASE_URL ?>uploads/prestasi/<?= $p['foto'] ?>" width="40" height="40" style="object-fit: cover;"><?php endif; ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning btn-edit"
                                data-id="<?= $p['id'] ?>"
                                data-siswa_id="<?= $p['siswa_id'] ?>"
                                data-siswa_text="<?= $p['nis'] . ' - ' . $p['nama'] ?>"
                                data-nama_prestasi="<?= htmlspecialchars($p['nama_prestasi']) ?>"
                                data-tingkat="<?= $p['tingkat_prestasi_id'] ?>"
                                data-juara="<?= $p['juara'] ?>"
                                data-tanggal="<?= $p['tanggal'] ?>"
                                data-penyelenggara="<?= htmlspecialchars($p['penyelenggara']) ?>"
                                data-keterangan="<?= htmlspecialchars($p['keterangan']) ?>">Edit</button>
                            <a href="proses.php?action=hapus&id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Prestasi -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="proses.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Prestasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label>Siswa</label>
                            <input type="text" name="siswa_search_edit" id="siswa_search_edit" class="form-control" list="siswaList" required>
                            <input type="hidden" name="siswa_id_edit" id="siswa_id_edit">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Nama Prestasi</label>
                            <input type="text" name="nama_prestasi" id="edit_nama_prestasi" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label>Tingkat</label>
                            <select name="tingkat_prestasi_id" id="edit_tingkat" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach ($tingkat_list as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= $t['nama'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label>Juara</label>
                            <input type="text" name="juara" id="edit_juara" class="form-control">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label>Tanggal</label>
                            <input type="date" name="tanggal" id="edit_tanggal" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Penyelenggara</label>
                            <input type="text" name="penyelenggara" id="edit_penyelenggara" class="form-control">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>Foto (kosongkan jika tidak ingin mengganti)</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-12 mb-2">
                            <label>Keterangan</label>
                            <input type="text" name="keterangan" id="edit_keterangan" class="form-control">
                        </div>
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
// Autocomplete form tambah
const siswaInput = document.getElementById('siswa_search');
const siswaIdHidden = document.getElementById('siswa_id');
siswaInput.addEventListener('input', function() {
    const val = this.value;
    const options = document.querySelectorAll('#siswaList option');
    let found = false;
    for (let opt of options) {
        if (opt.value === val) {
            siswaIdHidden.value = opt.dataset.id;
            found = true;
            break;
        }
    }
    if (!found) siswaIdHidden.value = '';
});

// Edit button
const editBtns = document.querySelectorAll('.btn-edit');
editBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit_id').value = this.dataset.id;
        document.getElementById('siswa_search_edit').value = this.dataset.siswa_text;
        document.getElementById('siswa_id_edit').value = this.dataset.siswa_id;
        document.getElementById('edit_nama_prestasi').value = this.dataset.nama_prestasi;
        document.getElementById('edit_tingkat').value = this.dataset.tingkat;
        document.getElementById('edit_juara').value = this.dataset.juara;
        document.getElementById('edit_tanggal').value = this.dataset.tanggal;
        document.getElementById('edit_penyelenggara').value = this.dataset.penyelenggara;
        document.getElementById('edit_keterangan').value = this.dataset.keterangan;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    });
});
// Autocomplete edit
const editSiswaInput = document.getElementById('siswa_search_edit');
const editSiswaIdHidden = document.getElementById('siswa_id_edit');
editSiswaInput.addEventListener('input', function() {
    const val = this.value;
    const options = document.querySelectorAll('#siswaList option');
    let found = false;
    for (let opt of options) {
        if (opt.value === val) {
            editSiswaIdHidden.value = opt.dataset.id;
            found = true;
            break;
        }
    }
    if (!found) editSiswaIdHidden.value = '';
});
</script>
<?php require_once '../../includes/footer.php'; ?>