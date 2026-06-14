# Sistem Informasi Manajemen MTs Al-Ihsan

Sistem Informasi Manajemen (SIM) Sekolah yang dirancang khusus untuk MTs Al-Ihsan. Aplikasi berbasis web ini membantu dalam pengelolaan data siswa, absensi, pelanggaran, prestasi, dan kebersihan kelas secara terintegrasi.

## 🚀 Fitur Utama

- **Dashboard Statistik**: Visualisasi data kehadiran, total siswa, dan peringkat kebersihan kelas secara real-time.
- **Manajemen Siswa & Kelas**: Pengelolaan data induk siswa dan pembagian rombongan belajar (rombel).
- **Sistem Absensi**: Pencatatan kehadiran harian siswa dengan laporan bulanan yang dapat diekspor.
- **Pencatatan Pelanggaran & Keterlambatan**: Monitoring kedisiplinan siswa dengan sistem poin/rekapitulasi.
- **Manajemen Prestasi**: Pendataan prestasi siswa lengkap dengan dokumentasi foto.
- **Penilaian Kebersihan**: Sistem peringkat kebersihan antar kelas untuk memotivasi siswa.
- **Ekspor Laporan**: Mendukung ekspor data ke format Excel menggunakan library PhpSpreadsheet.
- **Multi Tahun Ajaran**: Pengelolaan data yang dipisahkan berdasarkan tahun ajaran aktif.

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP (Native)
- **Database**: MySQL (PDO)
- **Frontend**: Bootstrap, FontAwesome, Chart.js
- **Dependencies**: [PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet)
- **Environment Management**: Custom `.env` loader

## 📋 Prasyarat Sistem

- PHP >= 8.0
- MySQL >= 5.7
- Composer
- Web Server (Apache/Nginx)

## 🔧 Instalasi

1. **Clone Repository**
   ```bash
   git clone https://github.com/username/mts-alihsan.git
   cd mts-alihsan
   ```

2. **Instal Dependensi**
   Pastikan Composer sudah terinstal di sistem Anda.
   ```bash
   composer install
   ```

3. **Konfigurasi Database**
   - Buat database baru di MySQL (misal: `mts_alihsan`).
   - Import file SQL yang tersedia di folder `modules/database/` atau root directory.

4. **Pengaturan Environment**
   Salin file `.env.example` menjadi `.env` dan sesuaikan konfigurasinya.
   ```bash
   cp config/.env.example .env
   ```
   Edit `.env`:
   ```env
   DB_HOST=localhost
   DB_NAME=mts_alihsan
   DB_USER=root
   DB_PASS=your_password
   BASE_URL=http://localhost/mts-alihsan/
   ```

5. **Izin Folder Upload**
   Pastikan folder `uploads/` memiliki izin tulis (write permission).
   ```bash
   chmod -R 775 uploads/
   ```

## 📁 Struktur Direktori

- `assets/`: File statis (CSS, JS, Images).
- `config/`: Konfigurasi database, router, dan fungsi global.
- `includes/`: Komponen layout (Header, Footer, Sidebar).
- `modules/`: Logika utama per fitur (Absensi, Pelanggaran, dll).
- `uploads/`: Media penyimpanan file yang diunggah (Foto Prestasi).
- `vendor/`: Library pihak ketiga via Composer.

## 🔒 Keamanan

Aplikasi ini dilengkapi dengan:
- `config/auth.php`: Proteksi sesi login.
- `.htaccess`: Pembatasan akses ke folder sensitif.
- `PDO`: Pencegahan SQL Injection dengan prepared statements.

## 📄 Lisensi

Proyek ini dikembangkan untuk kebutuhan internal MTs Al-Ihsan.

---
*Dikembangkan dengan integritas untuk kemajuan pendidikan.*
