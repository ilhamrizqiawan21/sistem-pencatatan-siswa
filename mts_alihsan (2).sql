-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 03 Bulan Mei 2026 pada 12.55
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mts_alihsan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi`
--

CREATE TABLE `absensi` (
  `id` int(10) UNSIGNED NOT NULL,
  `siswa_id` int(10) UNSIGNED NOT NULL,
  `tahun_ajaran_id` int(10) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('H','I','S','A') NOT NULL DEFAULT 'H',
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenis_pelanggaran`
--

CREATE TABLE `jenis_pelanggaran` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(150) NOT NULL,
  `poin` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `jenis_pelanggaran`
--

INSERT INTO `jenis_pelanggaran` (`id`, `nama`, `poin`) VALUES
(1, 'Tidak Membawa Alat Sholat', 5),
(2, 'Tidak Membawa Juz Amma', 5),
(3, 'Tidak Membawa Rok/Celana Ganti untuk Sholat', 5),
(4, 'Model Rambut Tidak Sesuai', 10),
(5, 'Memakai Atribut Sekolah Tidak Sesuai Standar', 10);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kebersihan_kelas`
--

CREATE TABLE `kebersihan_kelas` (
  `id` int(10) UNSIGNED NOT NULL,
  `kelas_id` int(10) UNSIGNED NOT NULL,
  `tahun_ajaran_id` int(10) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `nilai_lantai` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `nilai_sampah` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `nilai_rak` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `nilai_penataan` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `nilai_total` tinyint(3) UNSIGNED GENERATED ALWAYS AS (round((`nilai_lantai` + `nilai_sampah` + `nilai_rak` + `nilai_penataan`) / 4,0)) STORED,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kebersihan_kelas`
--

INSERT INTO `kebersihan_kelas` (`id`, `kelas_id`, `tahun_ajaran_id`, `tanggal`, `nilai_lantai`, `nilai_sampah`, `nilai_rak`, `nilai_penataan`, `keterangan`, `created_at`) VALUES
(1, 14, 2, '2026-05-03', 4, 1, 1, 1, '', '2026-05-03 07:59:26');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelas`
--

CREATE TABLE `kelas` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama_kelas` varchar(20) NOT NULL,
  `wali_kelas` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kelas`
--

INSERT INTO `kelas` (`id`, `nama_kelas`, `wali_kelas`, `created_at`) VALUES
(7, 'VIII-E', 'Ilham Rizqiawan, S.Pd.', '2026-05-02 13:39:01'),
(14, 'VII-A', 'Astri Yuliasari, S.Pd.', '2026-05-03 01:01:25'),
(15, 'VII-B', 'Shinta Nuryatna, S.Pd.', '2026-05-03 01:01:40'),
(16, 'VII-C', 'Tintin Agustini, S.Ag.', '2026-05-03 01:01:55'),
(17, 'VII-D', 'Jejen Jaenudin, S.Pd.', '2026-05-03 01:02:11'),
(18, 'VIII-A', 'Achmad Fathoni H., S.P.', '2026-05-03 01:02:31'),
(19, 'VIII-B', 'Siti Rahmah, S.Pd.', '2026-05-03 01:02:52'),
(20, 'VIII-C', 'Tatang Aruman Ekajaya, S.Pd.I.', '2026-05-03 01:03:09'),
(21, 'VIII-D', 'Rismaya Rachmat H., S.Hum.', '2026-05-03 01:03:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keterlambatan`
--

CREATE TABLE `keterlambatan` (
  `id` int(10) UNSIGNED NOT NULL,
  `siswa_id` int(10) UNSIGNED NOT NULL,
  `tahun_ajaran_id` int(10) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `jam_datang` time NOT NULL,
  `alasan` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `keterlambatan`
--

INSERT INTO `keterlambatan` (`id`, `siswa_id`, `tahun_ajaran_id`, `tanggal`, `jam_datang`, `alasan`, `keterangan`, `created_at`) VALUES
(1, 205, 2, '2026-05-04', '08:56:00', 'b', NULL, '2026-05-03 07:58:02'),
(2, 205, 2, '2026-05-03', '16:00:00', 'a', NULL, '2026-05-03 08:00:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelanggaran`
--

CREATE TABLE `pelanggaran` (
  `id` int(10) UNSIGNED NOT NULL,
  `siswa_id` int(10) UNSIGNED NOT NULL,
  `tahun_ajaran_id` int(10) UNSIGNED NOT NULL,
  `jenis_pelanggaran_id` int(10) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pelanggaran`
--

INSERT INTO `pelanggaran` (`id`, `siswa_id`, `tahun_ajaran_id`, `jenis_pelanggaran_id`, `tanggal`, `keterangan`, `created_at`) VALUES
(1, 205, 2, 5, '2026-05-04', '', '2026-05-03 07:56:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `prestasi`
--

CREATE TABLE `prestasi` (
  `id` int(10) UNSIGNED NOT NULL,
  `siswa_id` int(10) UNSIGNED NOT NULL,
  `tahun_ajaran_id` int(10) UNSIGNED NOT NULL,
  `nama_prestasi` varchar(200) NOT NULL,
  `tingkat_prestasi_id` int(10) UNSIGNED NOT NULL,
  `juara` varchar(50) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `penyelenggara` varchar(150) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `prestasi`
--

INSERT INTO `prestasi` (`id`, `siswa_id`, `tahun_ajaran_id`, `nama_prestasi`, `tingkat_prestasi_id`, `juara`, `tanggal`, `penyelenggara`, `foto`, `keterangan`, `created_at`) VALUES
(1, 205, 2, 'b', 2, '1', '2026-05-03', 'd', NULL, 'd', '2026-05-03 07:58:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `siswa`
--

CREATE TABLE `siswa` (
  `id` int(10) UNSIGNED NOT NULL,
  `nis` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas_id` int(10) UNSIGNED NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tempat_lahir` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp_ortu` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `siswa`
--

INSERT INTO `siswa` (`id`, `nis`, `nama`, `kelas_id`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `alamat`, `no_hp_ortu`, `foto`, `status`, `created_at`) VALUES
(1, '7101', 'ALIFIA NUR FAIZAH', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:53'),
(2, '7102', 'AQILLA RAISYA PUTRI', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:53'),
(3, '7103', 'FADLI MUKSIN KHAMIL', 14, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:53'),
(4, '7104', 'FAREL ANANDA PUTRA', 14, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:53'),
(5, '7105', 'FIRYAL ASKANA SAKHI', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:53'),
(6, '7106', 'HANUN NUR FADHILAH', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:54'),
(7, '7107', 'ICHA LATIFA', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:54'),
(8, '7108', 'KANAYA', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:54'),
(9, '7109', 'KHALIFA AMISHA SAGIRA', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:54'),
(10, '7110', 'KINARAISA AN-NAAFI WIRAWAN', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:54'),
(11, '7111', 'LABIB MU\'AZAM', 14, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:55'),
(12, '7112', 'M RIFKI KHOIRUL AKBAR', 14, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:55'),
(13, '7113', 'MAULANA IDRIS', 14, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:55'),
(14, '7117', 'MUHAMAD NURSATYA BOAN', 14, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:55'),
(15, '7118', 'MUHAMAD ZAYID RAMADHAN', 14, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:55'),
(16, '7114', 'MUHAMMAD DZAKIYY DZIKRULLAH', 14, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:55'),
(17, '7115', 'MUHAMMAD FADHILAH AL KHALIFI', 14, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:55'),
(18, '7116', 'MUHAMMAD FATAH AZRIANSYAH', 14, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:55'),
(19, '7119', 'NAILA SEPTIANA SAKIRA', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:55'),
(20, '7120', 'NAURA BILQIS ANSORI', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:55'),
(21, '7121', 'NAURA FAUZAHRA PUTRI', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:55'),
(22, '7122', 'QITSHI NUR FADILLAH', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:55'),
(23, '7124', 'RAISHA CHANTIKA APRILLIA', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:55'),
(24, '7123', 'RAISSA AQILA PUTRI', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:56'),
(25, '7125', 'RAISYA TALITA ZAHRAN', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:56'),
(26, '7127', 'SHAQUILLE FIDELYA AHMAD', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:56'),
(27, '7128', 'TEUKU MUHAMMAD ISKANDAR', 14, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:56'),
(28, '7129', 'WILDANSYAH ALFAZIO MUHAMMAD', 14, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:56'),
(29, '7130', 'YASMIN ZALFA RAQILLA PANGESTU', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:56'),
(30, '7131', 'YUHAN FAUSTINA ASGANI', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:56'),
(31, '7132', 'ZAHRA NUR ASYIFA', 14, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 04:20:56'),
(32, '7201', 'AGNIA SALIMATUS SADIAH', 15, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:22'),
(33, '7202', 'ALBY ARKAN MUSYAFFA', 15, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:23'),
(34, '7203', 'ALISHA RAHMA ADISTIA', 15, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:23'),
(35, '7204', 'ANIZAM KHOIRIL NUR AZMI', 15, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:23'),
(36, '7205', 'ARSYA SARA AL ZAHIRA', 15, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:23'),
(37, '7206', 'AZZAM MUWALIED', 15, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:24'),
(38, '7207', 'ENZI FITRAH HARDIANSYAH', 15, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:25'),
(39, '7208', 'EUIS GHINA KHOIRUNISA', 15, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:25'),
(40, '7209', 'FAIZA NABIL SAPUTRA', 15, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:25'),
(41, '7210', 'HAYFA AZARIA DIELLA', 15, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:25'),
(42, '7211', 'KARINA NUR AZIZAH', 15, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:25'),
(43, '7212', 'KHAIRA GHASSANY MAJID', 15, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:25'),
(44, '7213', 'M DAFA H R', 15, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:25'),
(45, '7214', 'MOCHAMMAD ALTAN MAHVIN DILARA', 15, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:25'),
(46, '7216', 'MUHAMAD ASRI ARYA AKBAR', 15, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:25'),
(47, '7220', 'MUHAMAD ZEEREN ZUBAIR AZAM', 15, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:25'),
(48, '7215', 'MUHAMMAD ARSYAD FAKHRULLAH', 15, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:25'),
(49, '7217', 'MUHAMMAD FADLI AN-NAWAWI', 15, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:25'),
(50, '7218', 'MUHAMMAD FAZAR MUTTAQIN', 15, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:25'),
(51, '7219', 'MUHAMMAD RAFI AL RASYID', 15, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:26'),
(52, '7221', 'NAFISA ARPIANI', 15, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:26'),
(53, '7222', 'NATASYA PUTRI SYAEPUDIN', 15, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:26'),
(54, '7223', 'NAYLA AZZAHRA', 15, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:26'),
(55, '7224', 'NAYLA RIFATUL ZAHRA', 15, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:26'),
(56, '7225', 'PRANANDA AHMAD FADILA', 15, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:26'),
(57, '7226', 'RAISA DEA ANANDA D', 15, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:26'),
(58, '7227', 'RAMDANI SAPUTRA', 15, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:26'),
(59, '7228', 'RASHDAN SYAM KOMARA', 15, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:26'),
(60, '7229', 'SALSABILA PUTRI FIRMANSYAH', 15, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:26'),
(61, '7230', 'SITI MARWAH KHAIRUNNISA', 15, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:26'),
(62, '7231', 'TASYA THALITA HASNA', 15, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:26'),
(63, '7232', 'ZAID AL-GHIFAR RAHARJA', 15, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:26'),
(64, '7301', 'ABBAS SYATHIR BAHRI', 16, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:26'),
(65, '7302', 'AIZHAR RAINER ZAIDAN', 16, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:27'),
(66, '7303', 'AMABEL JESSICA AZALIA', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:27'),
(67, '7304', 'DITA AINUNNISA', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:27'),
(68, '7305', 'FAISAL ZAKY AZ-ZAUHARI', 16, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:27'),
(69, '7306', 'FATHIR RIFFAD HARIRI', 16, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:27'),
(70, '7307', 'GAITSA JOAKIMA AUMAE', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:27'),
(71, '7308', 'ILMAN RAHMAT FAUZAAN', 16, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:27'),
(72, '7309', 'KAELA AZZAHRA KHAIRUNISA', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:27'),
(73, '7310', 'KEYSA PUTRI AISA', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:27'),
(74, '7311', 'KEYSHA SHAKEELA ALI', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:28'),
(75, '7312', 'KHANSA FATIHA ULFAH', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:28'),
(76, '7313', 'LEIDA ACQUEELIA AFEEFA', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:28'),
(77, '7315', 'MAHDA SYAKIRA RAMADHANI', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:28'),
(78, '7314', 'MUHAMAD ALQA AL HAFIZT', 16, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:28'),
(79, '7316', 'MUHAMMAD FAISAL MUSTOFA', 16, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:28'),
(80, '7317', 'MUHAMMAD RAFFA EL IBRAHIM', 16, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:28'),
(81, '7318', 'MUHAMMAD ZAIDAN', 16, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:28'),
(82, '7319', 'NADIRA AMANIA PERMADI', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:28'),
(83, '7320', 'NAILA APRILIA SYAKIRA', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:29'),
(84, '7321', 'NAUFAL ADITYA PRAWIRA', 16, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:29'),
(85, '7322', 'NAYLA AZALIA HAKIM', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:29'),
(86, '7323', 'NOVA MAHARANI NURFAJAR', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:29'),
(87, '7324', 'NUHA AUFA ASHILA', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:29'),
(88, '7325', 'PUTRI MUTIARA RAYA', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:29'),
(89, '7326', 'QIANDRA OKTA MAULANA', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:29'),
(90, '7327', 'RIFAA MARLIANI', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:29'),
(91, '7328', 'SALWA NUR RASHIFAH', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:29'),
(92, '7329', 'TEDYSYAH AHMAD FILANDO', 16, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:29'),
(93, '7330', 'WAFI NUR AIDAH', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:29'),
(94, '7331', 'ZAHIRA RAMADHANI', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:29'),
(95, '7332', 'ZAHRA ALMAIRA KHALISA', 16, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:29'),
(96, '7434', 'AFDHAL DARAJATULLOH', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:29'),
(97, '7401', 'AIRA SALSABILA AZ ZAHRA', 17, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:30'),
(98, '7402', 'ALDIO KENZIE ERLANGGA', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:30'),
(99, '7403', 'ARKA MAULANA RIZKI', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:30'),
(100, '7404', 'ATHARIZZ RIZKY PERMANA', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:30'),
(101, '7405', 'AZKA SEPTIAN ZULFIKAR', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:30'),
(102, '7406', 'DAFFA AL BUCHORI HERMAWAN', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:30'),
(103, '7407', 'FABIAN RADITHYA', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:30'),
(104, '7408', 'FAREEQ ZHAFIR MAULANA', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:30'),
(105, '7409', 'HANNA SYAKILA SUDRAJAT', 17, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:30'),
(106, '7410', 'INDRA NURAN FADHILAH', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:30'),
(107, '7411', 'KEISYA AULIA PUTRI', 17, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:31'),
(108, '7412', 'KHAIZAN MUHAMMAD RAFKA', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:32'),
(109, '7413', 'KIRANA APRILLA ANZANI PUTRI', 17, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:33'),
(110, '7414', 'M MARCHEL GUSTA ZHAHIR PERMANA', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:33'),
(111, '7415', 'MOHAMMAD AZKA ANUGRAH', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:33'),
(112, '7417', 'MUHAMAD ADAM YUDISTIRA SEPTRIYANA', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:33'),
(113, '7416', 'MUHAMAD RAIHAN NATA PUTRA KUSUMA', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:33'),
(114, '7420', 'MUHAMAD RIZKI ADITYA PUTRA', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:33'),
(115, '7418', 'MUHAMMAD AZZAM PRADIPTA ZEROUN', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:33'),
(116, '7419', 'MUHAMMAD RAFASYA AL FARIZHI', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:33'),
(117, '7421', 'NABIL ALVERO APRILIO', 17, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:33'),
(118, '7422', 'PRABU SETIA ARYANTA', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:33'),
(119, '7423', 'RAEHAN AL-KAHFI PUTRA', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:33'),
(120, '7424', 'RAISHA AQILA ZAHRA', 17, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:33'),
(121, '7427', 'SAHWA PUTRI TIHARA', 17, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:33'),
(122, '7426', 'SALMA FAUZIYAH', 17, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:34'),
(123, '7428', 'SILVIA', 17, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:34'),
(124, '7429', 'SITI MARIAM', 17, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:34'),
(125, '7433', 'TEUKU RENDRA GUNAWANSYAH', 17, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:34'),
(126, '7430', 'THORIQ PUTRA SURYA', 17, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:34'),
(127, '7431', 'ZAHRA NUR FAIQAH', 17, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:34'),
(128, '8101', 'AHMAD RIFAL', 18, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:34'),
(129, '8102', 'AILA MAIZA SURYA', 18, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:34'),
(130, '8103', 'ALVIN PUTRA ARIEF BILLAH', 18, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:34'),
(131, '8104', 'AZRIN ALYA HUSNA', 18, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:34'),
(132, '8105', 'CESIKA QALESYA NUGRAHA', 18, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:34'),
(133, '8106', 'DHESYA SITI JULAIKHA', 18, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:34'),
(134, '8107', 'FADLAH NURUL FAUZIAH', 18, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:34'),
(135, '8108', 'FAKHRI ZAIDAN NUR ALAM', 18, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:34'),
(136, '8109', 'FARHAN JAYA HARTANA', 18, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:34'),
(137, '8110', 'FATIHA NABILA ALFITRIA', 18, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:35'),
(138, '8111', 'HERLAN PERI PERDIANTO', 18, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:35'),
(139, '8112', 'JULIAN RIZKY RABBANA', 18, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:35'),
(140, '8113', 'MOCHAMAD RIZKY PANGISTU SURACHMAN', 18, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:35'),
(141, '8114', 'MUHAMAD SYAHDAN ALHAJ', 18, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:35'),
(142, '8116', 'MUHAMMAD NAJMU SALIM AR RIFA\'I', 18, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:35'),
(143, '8117', 'NADILLA MUTIARA AYU', 18, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:35'),
(144, '8118', 'NAFHIZA DIVIE ALZAIRA', 18, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:35'),
(145, '8119', 'NIZAM MUZAKI', 18, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:35'),
(146, '8120', 'RAMLI ANUGRAH', 18, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:35'),
(147, '8121', 'RANGGA MAULANA', 18, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:36'),
(148, '8122', 'RAYHAN PUTRA SOFYAN', 18, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:36'),
(149, '8123', 'RIBKA MEYLIANA', 18, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:36'),
(150, '8124', 'SALMA SALSABILA AZAHRA', 18, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:36'),
(151, '8125', 'SALSA FADHILAH', 18, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:36'),
(152, '8126', 'VIRA NOVIANTI', 18, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:36'),
(153, '8127', 'YANI KARTIKA', 18, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:36'),
(154, '8301', 'ADI PUTRA PERMANA', 20, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:36'),
(155, '8302', 'AFHIKA RAHMA YUNIAR', 20, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:36'),
(156, '8303', 'ALIEFA RIZKIA FADILAH', 20, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:36'),
(157, '8304', 'ALIYA QURROTA AYUNI', 20, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:36'),
(158, '8305', 'ARSI SULISTIAWATI', 20, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:36'),
(159, '8306', 'AZKHA ALEZSYA APRILIANIE', 20, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:37'),
(160, '8307', 'INAYAH MUTHMAINAH', 20, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:37'),
(161, '8308', 'IRDZAN FIRDANSYAH', 20, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:37'),
(162, '8309', 'IRSAN ALDY RISANDY', 20, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:37'),
(163, '8310', 'JIHAN NABILLA ZAHRA', 20, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:37'),
(164, '8311', 'KANIA PEBRIYANI', 20, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:37'),
(165, '8312', 'KEISYA ANASTASYA RUSNANDIKA', 20, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:37'),
(166, '8313', 'MAHADIRGA BINTANG RAMADHAN', 20, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:37'),
(167, '8314', 'MOCHAMMAD DAFFA ALFAUZY', 20, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:37'),
(168, '8315', 'MUHAMAD BRILIAN', 20, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:37'),
(169, '8316', 'MUHAMAD REVALDI', 20, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:37'),
(170, '8317', 'MUHAMMAD NUR FADILAH', 20, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:37'),
(171, '8318', 'MUHAMMAD SABIT MAULANA SIDIK', 20, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:37'),
(172, '8319', 'MUHAMMAD SOFIAN HADI', 20, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:38'),
(173, '8320', 'NAISYA ANANDA FAUZI', 20, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:38'),
(174, '8321', 'REISYA AMELIA PUTRI', 20, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:38'),
(175, '8322', 'SANNY MUHAMAD FADILAH', 20, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:38'),
(176, '8323', 'SYAFIRA NUR ANDRIANI', 20, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:38'),
(177, '8324', 'YUSFI AL FATH BANUN ANTONI', 20, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:38'),
(178, '8325', 'ZAHRA AULIA DARYANTI PUTRI', 20, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:38'),
(179, '8326', 'ZAMZAM REVARIZA', 20, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:38'),
(180, '8501', 'ADITYA RIZKI RAMDANI', 7, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:38'),
(181, '8502', 'AHMAD YASIN SYAFI\'I', 7, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:38'),
(182, '8503', 'ALAINA KAMILA', 7, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:38'),
(183, '8504', 'ALI FAISHAL ARSYAD', 7, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:38'),
(184, '8505', 'AQILA MUNAAZZAHRA', 7, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:38'),
(185, '8506', 'ASYIFA RAMADHANI', 7, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:38'),
(186, '8507', 'DELISTA PUTRI ANGGRAENI', 7, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:38'),
(187, '8508', 'FADHILLAH NUR HAKIM', 7, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:38'),
(188, '8509', 'FIKA APRILIANI', 7, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:39'),
(189, '8510', 'KINANTI PRAMISWARI', 7, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:39'),
(190, '8511', 'MAULIDINA RAISYA WIANA', 7, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:39'),
(191, '8512', 'MOCH ABYL AL BARIQ', 7, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:39'),
(192, '8513', 'MOCH DHIKA ERLANGGA', 7, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:39'),
(193, '8514', 'MUHAMAD FAHRI FAUZI', 7, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:39'),
(194, '8515', 'MUHAMAD RIZKY BAGJA ANUGRAH', 7, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:40'),
(195, '8516', 'MUHAMMAD AJRUN KABIR', 7, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:40'),
(196, '8517', 'MUHAMMAD AZKA ZAIDAN', 7, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:40'),
(197, '8518', 'MUHAMMAD IKHSAN KAMIL', 7, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:40'),
(198, '8519', 'NIRVANA OKTA PAMUNGKAS SUKASWO', 7, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:40'),
(199, '8520', 'NOVIA NISMARA RATADEWATI', 7, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:41'),
(200, '8521', 'PANDU ANUGRAH SAPUTRA', 7, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:41'),
(201, '8522', 'RAIHAN WIDHY PRATAMA', 7, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:44'),
(202, '8523', 'SALMA DESWITA MAHARANI', 7, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:46'),
(203, '8524', 'SILMI KAFFAH', 7, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:46'),
(204, '8525', 'SITI SAYYIDAH NUR RIZQIANTI', 7, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:46'),
(205, '8540', 'testing', 7, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:46'),
(206, '8526', 'TIRA ARTIKA SARI', 7, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:46'),
(207, '8527', 'TRIAD M HANAFI', 7, 'L', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:46'),
(208, '8528', 'YANI DAMAR ZAUHARIYAH', 7, 'P', NULL, NULL, NULL, NULL, NULL, 1, '2026-05-03 07:55:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `surat_izin`
--

CREATE TABLE `surat_izin` (
  `id` int(10) UNSIGNED NOT NULL,
  `siswa_id` int(10) UNSIGNED NOT NULL,
  `tahun_ajaran_id` int(10) UNSIGNED NOT NULL,
  `jenis_izin` enum('pulang','biasa') NOT NULL,
  `tanggal` date NOT NULL,
  `jam_berangkat` time DEFAULT NULL,
  `alasan_pulang` enum('sakit','keluarga','lomba','lainnya') DEFAULT NULL,
  `alasan_biasa` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `surat_izin`
--

INSERT INTO `surat_izin` (`id`, `siswa_id`, `tahun_ajaran_id`, `jenis_izin`, `tanggal`, `jam_berangkat`, `alasan_pulang`, `alasan_biasa`, `keterangan`, `created_at`) VALUES
(1, 205, 2, 'pulang', '2026-05-04', '14:59:00', 'sakit', NULL, '', '2026-05-03 07:59:59'),
(2, 205, 2, 'pulang', '2026-05-03', '17:00:00', 'sakit', NULL, 'b', '2026-05-03 08:00:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tahun_ajaran`
--

CREATE TABLE `tahun_ajaran` (
  `id` int(10) UNSIGNED NOT NULL,
  `tahun` varchar(9) NOT NULL,
  `semester` enum('1','2') NOT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tahun_ajaran`
--

INSERT INTO `tahun_ajaran` (`id`, `tahun`, `semester`, `is_aktif`, `created_at`) VALUES
(1, '2024/2025', '2', 0, '2026-05-02 10:56:02'),
(2, '2025/2026', '2', 1, '2026-05-03 03:56:58');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tingkat_prestasi`
--

CREATE TABLE `tingkat_prestasi` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tingkat_prestasi`
--

INSERT INTO `tingkat_prestasi` (`id`, `nama`) VALUES
(1, 'Sekolah'),
(2, 'Kecamatan'),
(3, 'Kabupaten/Kota'),
(4, 'Provinsi'),
(5, 'Nasional'),
(6, 'Internasional');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','guru','kepala_sekolah','siswa') NOT NULL DEFAULT 'admin',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, '2026-05-02 10:56:00');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_absensi` (`siswa_id`,`tanggal`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`);

--
-- Indeks untuk tabel `jenis_pelanggaran`
--
ALTER TABLE `jenis_pelanggaran`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kebersihan_kelas`
--
ALTER TABLE `kebersihan_kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`);

--
-- Indeks untuk tabel `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `keterlambatan`
--
ALTER TABLE `keterlambatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`);

--
-- Indeks untuk tabel `pelanggaran`
--
ALTER TABLE `pelanggaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  ADD KEY `jenis_pelanggaran_id` (`jenis_pelanggaran_id`);

--
-- Indeks untuk tabel `prestasi`
--
ALTER TABLE `prestasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  ADD KEY `tingkat_prestasi_id` (`tingkat_prestasi_id`);

--
-- Indeks untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indeks untuk tabel `surat_izin`
--
ALTER TABLE `surat_izin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`);

--
-- Indeks untuk tabel `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tingkat_prestasi`
--
ALTER TABLE `tingkat_prestasi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jenis_pelanggaran`
--
ALTER TABLE `jenis_pelanggaran`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `kebersihan_kelas`
--
ALTER TABLE `kebersihan_kelas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `keterlambatan`
--
ALTER TABLE `keterlambatan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `pelanggaran`
--
ALTER TABLE `pelanggaran`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `prestasi`
--
ALTER TABLE `prestasi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=209;

--
-- AUTO_INCREMENT untuk tabel `surat_izin`
--
ALTER TABLE `surat_izin`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `tingkat_prestasi`
--
ALTER TABLE `tingkat_prestasi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `absensi_ibfk_2` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`);

--
-- Ketidakleluasaan untuk tabel `kebersihan_kelas`
--
ALTER TABLE `kebersihan_kelas`
  ADD CONSTRAINT `kebersihan_kelas_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`),
  ADD CONSTRAINT `kebersihan_kelas_ibfk_2` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`);

--
-- Ketidakleluasaan untuk tabel `keterlambatan`
--
ALTER TABLE `keterlambatan`
  ADD CONSTRAINT `keterlambatan_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `keterlambatan_ibfk_2` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`);

--
-- Ketidakleluasaan untuk tabel `pelanggaran`
--
ALTER TABLE `pelanggaran`
  ADD CONSTRAINT `pelanggaran_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pelanggaran_ibfk_2` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`),
  ADD CONSTRAINT `pelanggaran_ibfk_3` FOREIGN KEY (`jenis_pelanggaran_id`) REFERENCES `jenis_pelanggaran` (`id`);

--
-- Ketidakleluasaan untuk tabel `prestasi`
--
ALTER TABLE `prestasi`
  ADD CONSTRAINT `prestasi_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prestasi_ibfk_2` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`),
  ADD CONSTRAINT `prestasi_ibfk_3` FOREIGN KEY (`tingkat_prestasi_id`) REFERENCES `tingkat_prestasi` (`id`);

--
-- Ketidakleluasaan untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`);

--
-- Ketidakleluasaan untuk tabel `surat_izin`
--
ALTER TABLE `surat_izin`
  ADD CONSTRAINT `fk_surat_izin_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_surat_izin_tahun` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
