<?php
$pageTitle = 'Data Siswa';
require_once '../../config/db.php';
require_once '../../config/auth.php';
require_once '../../config/functions.php';
require_once '../../includes/header.php';

// Tampilkan pesan import
if (isset($_SESSION['import_message'])) {
    echo '<div class="alert alert-info alert-dismissible fade show" role="alert">'
         . htmlspecialchars($_SESSION['import_message']) 
         . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    unset($_SESSION['import_message']);
}
if (isset($_SESSION['import_errors']) && is_array($_SESSION['import_errors'])) {
    echo '<div class="alert alert-warning alert-dismissible fade show"><strong>Beberapa baris gagal:</strong><ul>';
    foreach ($_SESSION['import_errors'] as $err) {
        echo '<li>' . htmlspecialchars($err) . '</li>';
    }
    echo '</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    unset($_SESSION['import_errors']);
}

$kelas_list = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas")->fetchAll();
$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;

// PERBAIKAN: tambahkan s.kelas_id ke SELECT
$sql = "SELECT s.id, s.nis, s.nama, s.jenis_kelamin, s.kelas_id, k.nama_kelas 
        FROM siswa s 
        JOIN kelas k ON s.kelas_id = k.id 
        WHERE s.status = 1";
$params = [];
if ($kelas_id > 0) {
    $sql .= " AND s.kelas_id = ?";
    $params[] = $kelas_id;
}
$sql .= " ORDER BY k.nama_kelas, s.nama";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$siswa = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between flex-wrap gap-2">
        <span><i class="fas fa-users"></i> Daftar Siswa</span>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalSiswa">
                <i class="fas fa-plus"></i> Tambah Siswa
            </button>
            <a href="export_template.php" class="btn btn-sm btn-success">
                <i class="fas fa-download"></i> Download Template
            </a>
            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalImport">
                <i class="fas fa-upload"></i> Import Excel
            </button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-auto">
                <select name="kelas_id" class="form-select" onchange="this.form.submit()">
                    <option value="0">-- Semua Kelas --</option>
                    <?php foreach ($kelas_list as $k): ?>
                        <option value="<?php echo $k['id']; ?>" <?php echo $kelas_id == $k['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($k['nama_kelas']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>NIS</th>
                        <th>Nama Lengkap</th>
                        <th>Kelas</th>
                        <th>Jenis Kelamin</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($siswa) > 0): ?>
                        <?php foreach ($siswa as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['nis']); ?></td>
                            <td><?php echo htmlspecialchars($s['nama']); ?></td>
                            <td><?php echo htmlspecialchars($s['nama_kelas']); ?></td>
                            <td><?php echo ($s['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning btn-edit" 
                                    data-id="<?php echo $s['id']; ?>"
                                    data-nis="<?php echo $s['nis']; ?>"
                                    data-nama="<?php echo $s['nama']; ?>"
                                    data-kelas="<?php echo $s['kelas_id']; ?>"
                                    data-jk="<?php echo $s['jenis_kelamin']; ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form method="POST" action="proses.php" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    <input type="hidden" name="action" value="hapus">
                                    <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-secondary">Belum ada data siswa.<?php echo ($kelas_id > 0) ? ' Kelas ini kosong.' : ''; ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Siswa -->
<div class="modal fade" id="modalSiswa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="proses.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-graduate"></i> Form Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="siswa_id">
                    <div class="mb-3">
                        <label class="form-label">NIS</label>
                        <input type="text" name="nis" id="nis" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" id="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <select name="kelas_id" id="kelas_id" class="form-select" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($kelas_list as $k): ?>
                                <option value="<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['nama_kelas']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" id="jenis_kelamin" class="form-select" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="modalImport" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="import_proses.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Import Data Siswa dari Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Pastikan file Excel berformat .xlsx atau .xls, dan kolom sesuai template: 
                        <strong>NIS, Nama, Kelas, Jenis Kelamin</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih File Excel</label>
                        <input type="file" name="file_excel" class="form-control" accept=".xlsx, .xls" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('siswa_id').value = this.dataset.id;
        document.getElementById('nis').value = this.dataset.nis;
        document.getElementById('nama').value = this.dataset.nama;
        document.getElementById('kelas_id').value = this.dataset.kelas;
        document.getElementById('jenis_kelamin').value = this.dataset.jk;
        new bootstrap.Modal(document.getElementById('modalSiswa')).show();
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
<!-- Modal Tambah/Edit Siswa -->
<div class="modal fade" id="modalSiswa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="proses.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-graduate"></i> Form Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="siswa_id">
                    <div class="mb-3">
                        <label class="form-label">NIS</label>
                        <input type="text" name="nis" id="nis" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" id="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <select name="kelas_id" id="kelas_id" class="form-select" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($kelas_list as $k): ?>
                                <option value="<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['nama_kelas']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" id="jenis_kelamin" class="form-select" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="modalImport" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="import_proses.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Import Data Siswa dari Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Pastikan file Excel berformat .xlsx atau .xls, dan kolom sesuai template: 
                        <strong>NIS, Nama, Kelas, Jenis Kelamin</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih File Excel</label>
                        <input type="file" name="file_excel" class="form-control" accept=".xlsx, .xls" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('siswa_id').value = this.dataset.id;
        document.getElementById('nis').value = this.dataset.nis;
        document.getElementById('nama').value = this.dataset.nama;
        document.getElementById('kelas_id').value = this.dataset.kelas;
        document.getElementById('jenis_kelamin').value = this.dataset.jk;
        new bootstrap.Modal(document.getElementById('modalSiswa')).show();
    });
});
</script>

    <?php require_once '../../includes/footer.php'; ?>
<?php require_once '../../includes/footer.php'; ?>