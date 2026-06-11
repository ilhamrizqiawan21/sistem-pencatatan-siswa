# Sistem Pencatatan Siswa

## Description / Deskripsi

**English:**
Sistem Pencatatan Siswa is a comprehensive student record management system for MTs. Al-Ihsan Batujajar. This application manages all aspects of student data including attendance, disciplinary records, tardiness, achievements, student roster, and early dismissal permissions. It provides a centralized solution for tracking and managing student information.

**Indonesia:**
Sistem Pencatatan Siswa adalah sistem manajemen catatan siswa yang komprehensif untuk MTs. Al-Ihsan Batujajar. Aplikasi ini mengelola semua aspek data siswa termasuk kehadiran, catatan pelanggaran, keterlambatan, prestasi, daftar siswa, dan izin pulang lebih awal. Ini menyediakan solusi terpusat untuk melacak dan mengelola informasi siswa.

---

## Installation / Instalasi

### Requirements / Persyaratan:
- PHP (v7.4 or higher / v7.4 atau lebih tinggi)
- Composer
- MySQL atau PostgreSQL
- Laravel Framework
- Node.js (untuk asset compilation / kompilasi aset)

### Steps / Langkah-langkah:

**English:**
1. Clone the repository:
   ```bash
   git clone https://github.com/ilhamrizqiawan21/sistem-pencatatan-siswa.git
   cd sistem-pencatatan-siswa
   ```

2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

3. Setup environment file:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure database and run migrations:
   ```bash
   php artisan migrate
   ```

5. Compile front-end assets:
   ```bash
   npm run dev
   ```

6. Start the application:
   ```bash
   php artisan serve
   ```

**Indonesia:**
1. Clone repository:
   ```bash
   git clone https://github.com/ilhamrizqiawan21/sistem-pencatatan-siswa.git
   cd sistem-pencatatan-siswa
   ```

2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

3. Setup file environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Konfigurasi database dan jalankan migrasi:
   ```bash
   php artisan migrate
   ```

5. Kompilasi aset front-end:
   ```bash
   npm run dev
   ```

6. Jalankan aplikasi:
   ```bash
   php artisan serve
   ```

---

## Features & Usage / Fitur & Penggunaan

### English:

**Features:**
- **Student Attendance Management** - Track daily attendance and generate attendance reports
- **Disciplinary Records** - Record and monitor student violations and penalties
- **Tardiness Tracking** - Log and analyze student tardiness patterns
- **Achievement Management** - Record student accomplishments and awards
- **Student Roster** - Comprehensive student enrollment and profile management
- **Early Dismissal Permissions** - Manage and track early dismissal requests

**Usage:**
1. Login to the administration panel
2. Select the feature you want to use (Attendance, Discipline, etc.)
3. Record or view student information
4. Generate reports as needed
5. Export data for administrative records

### Indonesia:

**Fitur:**
- **Manajemen Kehadiran Siswa** - Lacak kehadiran harian dan buat laporan kehadiran
- **Catatan Pelanggaran** - Catat dan pantau pelanggaran siswa dan hukuman
- **Pelacakan Keterlambatan** - Catat dan analisis pola keterlambatan siswa
- **Manajemen Prestasi** - Catat pencapaian dan penghargaan siswa
- **Daftar Siswa** - Manajemen pendaftaran dan profil siswa yang komprehensif
- **Izin Pulang Lebih Awal** - Kelola dan lacak permintaan pulang lebih awal

**Penggunaan:**
1. Login ke panel administrasi
2. Pilih fitur yang ingin Anda gunakan (Kehadiran, Pelanggaran, dll)
3. Catat atau lihat informasi siswa
4. Buat laporan sesuai kebutuhan
5. Ekspor data untuk catatan administratif

---

## Author / Penulis
Ilham Rizqiawan

## License / Lisensi
This project is licensed under the MIT License - see the LICENSE file for details.
