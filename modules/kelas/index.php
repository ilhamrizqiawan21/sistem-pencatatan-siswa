<?php
$pageTitle = 'Manajemen Kelas';
require_once '../../config/db.php';
require_once '../../includes/header.php';

$stmt = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas");
$kelas = $stmt->fetchAll();
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-door-open me-2"></i> Data Kelas</span>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalKelas">
            <i class="fas fa-plus"></i> Tambah Kelas
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Kelas</th>
                        <th>Wali Kelas</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($kelas as $k): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($k['nama_kelas']) ?></td>
                        <td><?= htmlspecialchars($k['wali_kelas']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning btn-edit" 
                                    data-id="<?= $k['id'] ?>" 
                                    data-nama="<?= $k['nama_kelas'] ?>" 
                                    data-wali="<?= $k['wali_kelas'] ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <a href="proses.php?action=hapus&id=<?= $k['id'] ?>" 
                               onclick="return confirmDelete(this.href)" 
                               class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (count($kelas) == 0): ?>
                    <tr>
                        <td colspan="4" class="text-center text-secondary">Belum ada data kelas.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="modalKelas" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-door-open me-2"></i> Form Kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                <div class="modal-body">
                    <input type="hidden" name="id" id="kelas_id">
                    <div class="mb-3">
                        <label class="form-label">Nama Kelas</label>
                        <input type="text" name="nama_kelas" id="nama_kelas" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Wali Kelas</label>
                        <input type="text" name="wali_kelas" id="wali_kelas" class="form-control">
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

<script>
document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('kelas_id').value = this.dataset.id;
        document.getElementById('nama_kelas').value = this.dataset.nama;
        document.getElementById('wali_kelas').value = this.dataset.wali;
        new bootstrap.Modal(document.getElementById('modalKelas')).show();
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>