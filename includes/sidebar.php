<?php
$current_uri = $_SERVER['REQUEST_URI'];

/**
 * Cek apakah path saat ini cocok dengan salah satu path yang diberikan.
 * Menggunakan perbandingan eksak sebelum fallback ke strpos untuk keamanan.
 */
function isActive($paths): string {
    global $current_uri;
    $uri = strtok($current_uri, '?'); // abaikan query string
    foreach ((array) $paths as $path) {
        // Remove .php extension for comparison
        $path = str_replace('.php', '', $path);
        if (strpos($uri, $path) !== false) {
            return 'active';
        }
    }
    return '';
}

/**
 * Cek apakah submenu harus terbuka (salah satu anaknya aktif).
 */
function isSubmenuOpen($paths): bool {
    return isActive($paths) === 'active';
}

$menuAbsensi   = ['absensi'];
$menuIzin      = ['izin'];
$menuMaster    = ['kelas', 'siswa'];
$menuPengaturan = ['pengaturan', 'ekspor'];
?>

<aside class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <img src="<?= BASE_URL ?>assets/img/logo-mts.png"
             alt="Logo MTs Al-Ihsan"
             class="sidebar-logo"
             onerror="this.style.display='none'">
        <div class="sidebar-brand-text">
            <span class="brand-name">MTs Al-Ihsan</span>
            <span class="brand-sub">Sistem Pencatatan</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav" aria-label="Menu Utama">
        <ul>

            <!-- Dashboard -->
            <li class="nav-item">
                <a href="<?= url('dashboard') ?>"
                   class="nav-link <?= isActive(['/mts-alihsan/', 'mts-alihsan/dashboard']) && !isActive(['absensi', 'izin', 'kelas', 'siswa', 'pelanggaran', 'keterlambatan', 'prestasi', 'kebersihan', 'pengaturan']) ? 'active' : '' ?>">
                    <span class="nav-icon"><i class="fas fa-gauge-high"></i></span>
                    <span class="nav-label">Dashboard</span>
                </a>
            </li>

            <li class="nav-section-label">Data Akademik</li>

            <!-- Master Data -->
            <li class="nav-item has-sub <?= isSubmenuOpen($menuMaster) ? 'open' : '' ?>">
                <button class="nav-link nav-toggle" aria-expanded="<?= isSubmenuOpen($menuMaster) ? 'true' : 'false' ?>">
                    <span class="nav-icon"><i class="fas fa-database"></i></span>
                    <span class="nav-label">Master Data</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </button>
                <ul class="sub-menu <?= isSubmenuOpen($menuMaster) ? 'open' : '' ?>">
                    <li>
                        <a href="<?= moduleUrl('kelas') ?>"
                           class="nav-link sub <?= isActive(['kelas']) ?>">
                            <span class="nav-icon"><i class="fas fa-door-open"></i></span>
                            <span class="nav-label">Kelas</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= moduleUrl('siswa') ?>"
                           class="nav-link sub <?= isActive(['siswa']) ?>">
                            <span class="nav-icon"><i class="fas fa-users"></i></span>
                            <span class="nav-label">Siswa</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Absensi -->
            <li class="nav-item has-sub <?= isSubmenuOpen($menuAbsensi) ? 'open' : '' ?>">
                <button class="nav-link nav-toggle" aria-expanded="<?= isSubmenuOpen($menuAbsensi) ? 'true' : 'false' ?>">
                    <span class="nav-icon"><i class="fas fa-calendar-check"></i></span>
                    <span class="nav-label">Absensi</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </button>
                <ul class="sub-menu <?= isSubmenuOpen($menuAbsensi) ? 'open' : '' ?>">
                    <li>
                        <a href="<?= moduleUrl('absensi') ?>"
                           class="nav-link sub <?= isActive(['absensi/index']) ?>">
                            <span class="nav-icon"><i class="fas fa-check-circle"></i></span>
                            <span class="nav-label">Absensi Harian</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= moduleUrl('absensi', 'absensi_bulanan') ?>"
                           class="nav-link sub <?= isActive(['absensi_bulanan']) ?>">
                            <span class="nav-icon"><i class="fas fa-calendar-week"></i></span>
                            <span class="nav-label">Absensi Bulanan</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= moduleUrl('absensi', 'laporan') ?>"
                           class="nav-link sub <?= isActive(['absensi/laporan']) ?>">
                            <span class="nav-icon"><i class="fas fa-chart-line"></i></span>
                            <span class="nav-label">Laporan Absensi</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= moduleUrl('absensi', 'rekap_siswa') ?>"
                           class="nav-link sub <?= isActive(['absensi/rekap_siswa']) ?>">
                            <span class="nav-icon"><i class="fas fa-user-clock"></i></span>
                            <span class="nav-label">Detail Per Siswa</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-section-label">Pencatatan</li>

            <!-- Pelanggaran -->
            <li class="nav-item">
                <a href="<?= moduleUrl('pelanggaran') ?>"
                   class="nav-link <?= isActive(['pelanggaran']) ?>">
                    <span class="nav-icon"><i class="fas fa-gavel"></i></span>
                    <span class="nav-label">Pelanggaran</span>
                </a>
            </li>

            <!-- Keterlambatan -->
            <li class="nav-item">
                <a href="<?= moduleUrl('keterlambatan') ?>"
                   class="nav-link <?= isActive(['keterlambatan']) ?>">
                    <span class="nav-icon"><i class="fas fa-clock"></i></span>
                    <span class="nav-label">Keterlambatan</span>
                </a>
            </li>

            <!-- Prestasi -->
            <li class="nav-item">
                <a href="<?= moduleUrl('prestasi') ?>"
                   class="nav-link <?= isActive(['prestasi']) ?>">
                    <span class="nav-icon"><i class="fas fa-trophy"></i></span>
                    <span class="nav-label">Prestasi</span>
                </a>
            </li>

            <!-- Kebersihan -->
            <li class="nav-item">
                <a href="<?= moduleUrl('kebersihan') ?>"
                   class="nav-link <?= isActive(['kebersihan']) ?>">
                    <span class="nav-icon"><i class="fas fa-broom"></i></span>
                    <span class="nav-label">Kebersihan Kelas</span>
                </a>
            </li>

            <!-- Surat Izin -->
            <li class="nav-item has-sub <?= isSubmenuOpen($menuIzin) ? 'open' : '' ?>">
                <button class="nav-link nav-toggle" aria-expanded="<?= isSubmenuOpen($menuIzin) ? 'true' : 'false' ?>">
                    <span class="nav-icon"><i class="fas fa-envelope-open-text"></i></span>
                    <span class="nav-label">Surat Izin</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </button>
                <ul class="sub-menu <?= isSubmenuOpen($menuIzin) ? 'open' : '' ?>">
                    <li>
                        <a href="<?= moduleUrl('izin') ?>"
                           class="nav-link sub <?= isActive(['izin/index']) ?>">
                            <span class="nav-icon"><i class="fas fa-plus-circle"></i></span>
                            <span class="nav-label">Input Izin</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= moduleUrl('izin', 'laporan') ?>"
                           class="nav-link sub <?= isActive(['izin/laporan']) ?>">
                            <span class="nav-icon"><i class="fas fa-chart-bar"></i></span>
                            <span class="nav-label">Laporan Izin</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= moduleUrl('izin', 'rekap_siswa') ?>"
                           class="nav-link sub <?= isActive(['izin/rekap_siswa']) ?>">
                            <span class="nav-icon"><i class="fas fa-user-clock"></i></span>
                            <span class="nav-label">Detail Per Siswa</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-section-label">Sistem</li>

            <!-- Pengaturan -->
            <li class="nav-item has-sub <?= isSubmenuOpen($menuPengaturan) ? 'open' : '' ?>">
                <button class="nav-link nav-toggle" aria-expanded="<?= isSubmenuOpen($menuPengaturan) ? 'true' : 'false' ?>">
                    <span class="nav-icon"><i class="fas fa-cog"></i></span>
                    <span class="nav-label">Pengaturan</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </button>
                <ul class="sub-menu <?= isSubmenuOpen($menuPengaturan) ? 'open' : '' ?>">
                    <li>
                        <a href="<?= moduleUrl('pengaturan') ?>"
                           class="nav-link sub <?= isActive(['pengaturan']) ?>">
                            <span class="nav-icon"><i class="fas fa-calendar-alt"></i></span>
                            <span class="nav-label">Tahun Ajaran</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= moduleUrl('ekspor') ?>"
                           class="nav-link sub <?= isActive(['ekspor']) ?>">
                            <span class="nav-icon"><i class="fas fa-file-excel"></i></span>
                            <span class="nav-label">Ekspor Data</span>
                        </a>
                    </li>
                </ul>
            </li>

        </ul>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <a href="<?= url('logout') ?>" class="sidebar-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>