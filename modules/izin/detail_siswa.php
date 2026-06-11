<?php
$pageTitle = 'Detail Surat Izin Siswa';
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../../includes/header.php';

$siswa_id = (int)($_GET['siswa_id'] ?? 0);
$bulan = (int)($_GET['bulan'] ?? date('m'));
$tahun = (int)($_GET['tahun'] ?? date('Y'));

if ($siswa_id == 0) {
    // pilih siswa dulu
    $siswa_list = $pdo->query("SELECT s.id, s.nis, s.nama, k.nama_kelas FROM siswa s JOIN kelas k ON s.kelas_id = k.id ORDER BY k.nama_kelas, s.nama")->fetchAll();
    ?>
    <div class="card"><div class="card-header">Pilih Siswa</div><div class="card-body"><form method="GET"><select name="siswa_id" class="form-select" required><option value="">-- Pilih --</option><?php foreach($siswa_list as $s): ?><option value="<?=$s['id']?>"><?=$s['nis']?> - <?=$s['nama']?> (<?=$s['nama_kelas']?>)</option><?php endforeach; ?></select><button type="submit" class="btn btn-primary mt-2">Tampilkan</button></form></div></div>
    <?php
} else {
    $siswa = $pdo->prepare("SELECT s.*, k.nama_kelas FROM siswa s JOIN kelas k ON s.kelas_id = k.id WHERE s.id = ?");
    $siswa->execute([$siswa_id]);
    $siswa = $siswa->fetch();
    if (!$siswa) die("Siswa tidak ditemukan");
    $start = "$tahun-$bulan-01";
    $end = date("Y-m-t", strtotime($start));
    $stmt = $pdo->prepare("SELECT * FROM surat_izin WHERE siswa_id = ? AND tanggal BETWEEN ? AND ? ORDER BY tanggal DESC");
    $stmt->execute([$siswa_id, $start, $end]);
    $izin = $stmt->fetchAll();
    ?>
    <div class="card"><div class="card-header">Detail Izin: <?=htmlspecialchars($siswa['nama'])?> (<?=$siswa['nis']?>) - <?=$siswa['nama_kelas']?></div><div class="card-body table-responsive">
        <table class="table"><thead><tr><th>Tanggal</th><th>Jenis</th><th>Alasan</th><th>Keterangan</th></tr></thead>
        <tbody><?php foreach($izin as $i): ?><tr><td><?=date('d-m-Y', strtotime($i['tanggal']))?></td><td><?=$i['jenis_izin']=='pulang'?'Izin Pulang':'Izin Biasa'?></td><td><?=($i['jenis_izin']=='pulang') ? $i['alasan_pulang'] . (isset($i['jam_berangkat'])?" (Jam: $i[jam_berangkat])":"") : htmlspecialchars($i['alasan_biasa'])?></td><td><?=htmlspecialchars($i['keterangan'])?></td></tr><?php endforeach; ?></tbody></table>
        <a href="laporan.php" class="btn btn-secondary">Kembali</a>
    </div></div>
    <?php
}
require_once '../../includes/footer.php';
?>