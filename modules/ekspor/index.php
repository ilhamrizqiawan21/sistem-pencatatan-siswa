<?php
$pageTitle = 'Ekspor Data';
require_once '../../config/db.php';
require_once '../../config/auth.php';
require_once '../../includes/header.php';
require_once '../../config/functions.php';

$tahun_aktif = getTahunAjaranAktif($pdo);
$ta_id = $tahun_aktif['id'] ?? 0;

$kelas_list = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas")->fetchAll();

// Inisialisasi siswa berdasarkan kelas yang mungkin sudah dipilih (via GET)
$selected_kelas = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
$selected_siswa = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;

$siswa_list = [];
if ($selected_kelas > 0) {
    $stmt = $pdo->prepare("SELECT id, nis, nama FROM siswa WHERE kelas_id = ? AND status = 1 ORDER BY nama");
    $stmt->execute([$selected_kelas]);
    $siswa_list = $stmt->fetchAll();
} else {
    $siswa_list = $pdo->query("SELECT id, nis, nama FROM siswa WHERE status = 1 ORDER BY nama")->fetchAll();
}

$thn_sekarang = date('Y');
$bln_sekarang = date('m');
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-download me-2"></i> Ekspor Data Sistem
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Pilih jenis data dan format ekspor. Gunakan filter rentang bulan/tahun untuk data periodik (absensi, pelanggaran, keterlambatan, prestasi). Kosongkan rentang untuk mengambil semua data.
        </div>

        <!-- Form Ekspor Excel -->
        <form method="get" action="excel.php" id="formExcel" target="_blank">
            <input type="hidden" name="format" value="excel">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Jenis Data</label>
                    <select name="mod" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <option value="pelanggaran">Pelanggaran Siswa</option>
                        <option value="keterlambatan">Keterlambatan Siswa</option>
                        <option value="prestasi">Prestasi Siswa</option>
                        <option value="absensi">Rekap Absensi</option>
                        <option value="siswa">Data Siswa</option>
                        <option value="kelas">Data Kelas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter Kelas</label>
                    <select name="kelas_id" id="filter_kelas_excel" class="form-select">
                        <option value="0">-- Semua Kelas --</option>
                        <?php foreach ($kelas_list as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= ($selected_kelas == $k['id']) ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter Siswa</label>
                    <select name="siswa_id" id="filter_siswa_excel" class="form-select">
                        <option value="0">-- Semua Siswa --</option>
                        <?php foreach ($siswa_list as $ss): ?>
                            <option value="<?= $ss['id'] ?>" <?= ($selected_siswa == $ss['id']) ? 'selected' : '' ?>><?= $ss['nis'] ?> - <?= htmlspecialchars($ss['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Rentang Tanggal (mulai)</label>
                    <div class="row g-1">
                        <div class="col-6">
                            <select name="bulan_mulai" class="form-select">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= ($m == 1) ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <select name="tahun_mulai" class="form-select">
                                <?php for ($y = $thn_sekarang-2; $y <= $thn_sekarang+1; $y++): ?>
                                    <option value="<?= $y ?>" <?= ($y == $thn_sekarang-1) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Rentang Tanggal (selesai)</label>
                    <div class="row g-1">
                        <div class="col-6">
                            <select name="bulan_selesai" class="form-select">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= ($m == $bln_sekarang) ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <select name="tahun_selesai" class="form-select">
                                <?php for ($y = $thn_sekarang-2; $y <= $thn_sekarang+1; $y++): ?>
                                    <option value="<?= $y ?>" <?= ($y == $thn_sekarang) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Ekspor Excel
                    </button>
                </div>
            </div>
        </form>

        <hr>

        <!-- Form Cetak / HTML -->
        <form method="get" action="html_print.php" id="formHtml" target="_blank">
            <input type="hidden" name="format" value="html">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Jenis Data</label>
                    <select name="mod" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <option value="pelanggaran">Pelanggaran Siswa</option>
                        <option value="keterlambatan">Keterlambatan Siswa</option>
                        <option value="prestasi">Prestasi Siswa</option>
                        <option value="absensi">Rekap Absensi</option>
                        <option value="siswa">Data Siswa</option>
                        <option value="kelas">Data Kelas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter Kelas</label>
                    <select name="kelas_id" id="filter_kelas_html" class="form-select">
                        <option value="0">-- Semua Kelas --</option>
                        <?php foreach ($kelas_list as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= ($selected_kelas == $k['id']) ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter Siswa</label>
                    <select name="siswa_id" id="filter_siswa_html" class="form-select">
                        <option value="0">-- Semua Siswa --</option>
                        <?php foreach ($siswa_list as $ss): ?>
                            <option value="<?= $ss['id'] ?>" <?= ($selected_siswa == $ss['id']) ? 'selected' : '' ?>><?= $ss['nis'] ?> - <?= htmlspecialchars($ss['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Rentang Tanggal (mulai)</label>
                    <div class="row g-1">
                        <div class="col-6"><select name="bulan_mulai" class="form-select"><?php for($m=1;$m<=12;$m++) echo '<option value="'.$m.'">'.date('F',mktime(0,0,0,$m,1)).'</option>'; ?></select></div>
                        <div class="col-6"><select name="tahun_mulai" class="form-select"><?php for($y=$thn_sekarang-2;$y<=$thn_sekarang+1;$y++) echo '<option value="'.$y.'">'.$y.'</option>'; ?></select></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Rentang Tanggal (selesai)</label>
                    <div class="row g-1">
                        <div class="col-6"><select name="bulan_selesai" class="form-select"><?php for($m=1;$m<=12;$m++) echo '<option value="'.$m.'">'.date('F',mktime(0,0,0,$m,1)).'</option>'; ?></select></div>
                        <div class="col-6"><select name="tahun_selesai" class="form-select"><?php for($y=$thn_sekarang-2;$y<=$thn_sekarang+1;$y++) echo '<option value="'.$y.'">'.$y.'</option>'; ?></select></div>
                    </div>
                </div>
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-print"></i> Cetak / HTML
                    </button>
                </div>
            </div>
        </form>

        <hr>
        <div class="text-muted small">
            <i class="fas fa-lightbulb"></i> Catatan: 
            <ul>
                <li>Ekspor Excel menggunakan PHPSpreadsheet. Pastikan terinstal.</li>
                <li>Ekspor HTML membuka halaman baru yang siap dicetak (Ctrl+P).</li>
                <li>Filter rentang tanggal berlaku untuk data yang memiliki tanggal (pelanggaran, keterlambatan, prestasi, absensi). Untuk data master (siswa, kelas), filter rentang diabaikan.</li>
                <li>Filter siswa akan membatasi data hanya untuk siswa tertentu (jika dipilih).</li>
            </ul>
        </div>
    </div>
</div>

<script>
// Fungsi untuk mengupdate dropdown siswa berdasarkan kelas yang dipilih
function updateSiswaByKelas(selectSiswaId, kelasId, selectedSiswaId = 0) {
    fetch('get_siswa_by_kelas.php?kelas_id=' + kelasId + '&siswa_terpilih=' + selectedSiswaId)
        .then(response => response.json())
        .then(data => {
            let select = document.getElementById(selectSiswaId);
            select.innerHTML = '<option value="0">-- Semua Siswa --</option>';
            data.forEach(siswa => {
                let option = document.createElement('option');
                option.value = siswa.id;
                option.textContent = siswa.nis + ' - ' + siswa.nama;
                if (siswa.id == selectedSiswaId) option.selected = true;
                select.appendChild(option);
            });
        });
}

// Elemen-elemen filter
const kelasExcel = document.getElementById('filter_kelas_excel');
const siswaExcel = document.getElementById('filter_siswa_excel');
const kelasHtml = document.getElementById('filter_kelas_html');
const siswaHtml = document.getElementById('filter_siswa_html');

// Event: perubahan kelas di form Excel
if (kelasExcel) {
    kelasExcel.addEventListener('change', function() {
        let kelasId = this.value;
        let selectedSiswa = siswaExcel.value;
        updateSiswaByKelas('filter_siswa_excel', kelasId, selectedSiswa);
        // Sinkronkan kelas di form HTML
        if (kelasHtml) kelasHtml.value = kelasId;
        // Trigger perubahan kelas di form HTML agar siswa di form HTML ikut berubah
        if (kelasHtml) kelasHtml.dispatchEvent(new Event('change'));
    });
}

// Event: perubahan kelas di form HTML
if (kelasHtml) {
    kelasHtml.addEventListener('change', function() {
        let kelasId = this.value;
        let selectedSiswa = siswaHtml.value;
        updateSiswaByKelas('filter_siswa_html', kelasId, selectedSiswa);
        // Sinkronkan kelas di form Excel
        if (kelasExcel) kelasExcel.value = kelasId;
    });
}

// Inisialisasi: jika ada kelas yang sudah dipilih (dari GET), jalankan update untuk kedua form
document.addEventListener('DOMContentLoaded', function() {
    if (kelasExcel && kelasExcel.value != 0) {
        updateSiswaByKelas('filter_siswa_excel', kelasExcel.value, <?= $selected_siswa ?>);
    }
    if (kelasHtml && kelasHtml.value != 0) {
        updateSiswaByKelas('filter_siswa_html', kelasHtml.value, <?= $selected_siswa ?>);
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>