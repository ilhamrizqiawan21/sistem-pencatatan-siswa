<?php
$pageTitle = 'Pengaturan Tahun Ajaran';
require_once '../../config/db.php';
require_once '../../includes/header.php';

$tahun_ajaran = $pdo->query("SELECT * FROM tahun_ajaran ORDER BY tahun DESC, semester DESC")->fetchAll();
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['set_aktif'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        header('Location: index.php?error=Request%20tidak%20valid');
        exit;
    }
    $id = $_POST['id'];
    $pdo->prepare("UPDATE tahun_ajaran SET is_aktif=0")->execute();
    $pdo->prepare("UPDATE tahun_ajaran SET is_aktif=1 WHERE id=?")->execute([$id]);
    header('Location: index.php');
    exit;
}
?>
<div class="card">
    <div class="card-header d-flex justify-content-between"><span>Tahun Ajaran</span><button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTA">Tambah Baru</button></div>
    <div class="card-body">
        <table class="table">
            <thead><tr><th>Tahun</th><th>Semester</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody><?php foreach($tahun_ajaran as $ta): ?><tr><td><?= $ta['tahun'] ?></td><td><?= $ta['semester'] ?></td><td><?= $ta['is_aktif'] ? '<span class="badge bg-success">Aktif</span>' : '' ?></td><td><?php if(!$ta['is_aktif']): ?><form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>"><input type="hidden" name="id" value="<?= $ta['id'] ?>"><button type="submit" name="set_aktif" class="btn btn-sm btn-primary">Aktifkan</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody>
        </table>
    </div>
</div>
<!-- Modal Tambah TA (sederhana) -->
<div class="modal fade" id="modalTA"><div class="modal-dialog"><form method="POST" action="proses.php"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>"><div class="modal-content"><div class="modal-header"><h5>Tambah Tahun Ajaran</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="text" name="tahun" placeholder="2024/2025" class="form-control mb-2" required><select name="semester" class="form-select"><option value="1">Semester 1</option><option value="2">Semester 2</option></select></div><div class="modal-footer"><button type="submit" name="simpan" class="btn btn-primary">Simpan</button></div></div></form></div></div>
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="fas fa-user-shield"></i> Profil Admin
            </div>
            <div class="card-body">
                <p>Kelola nama, username, dan password akun admin.</p>
                <a href="profil_admin.php" class="btn btn-primary">Edit Profil Admin</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>