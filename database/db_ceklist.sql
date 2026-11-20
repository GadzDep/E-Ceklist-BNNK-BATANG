-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 19, 2026 at 10:01 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_ceklist`
--

-- --------------------------------------------------------

--
-- Table structure for table `ceklist_detail`
--

CREATE TABLE `ceklist_detail` (
  `id` int(11) NOT NULL,
  `ceklist_harian_id` int(11) NOT NULL,
  `jam_cek` enum('08:00','11:00','14:00') NOT NULL,
  `indikator_id` int(11) NOT NULL,
  `status` enum('Bersih/Rapi','Kotor/Perlu Tindakan') NOT NULL,
  `foto_bukti` varchar(255) DEFAULT NULL,
  `nama_petugas` varchar(120) NOT NULL DEFAULT '',
  `petugas_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ceklist_harian`
--

CREATE TABLE `ceklist_harian` (
  `id` int(11) NOT NULL,
  `ruangan_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `catatan_temuan` text DEFAULT NULL,
  `verif_pj_sarpras` tinyint(1) NOT NULL DEFAULT 0,
  `waktu_verif_pj` datetime DEFAULT NULL,
  `nama_verifikator_pj` varchar(120) DEFAULT NULL,
  `verif_kasubbag` tinyint(1) NOT NULL DEFAULT 0,
  `waktu_verif_kasubbag` datetime DEFAULT NULL,
  `nama_verifikator_kasubbag` varchar(120) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `indikator_ruangan`
--

CREATE TABLE `indikator_ruangan` (
  `id` int(11) NOT NULL,
  `ruangan_id` int(11) NOT NULL,
  `nama_indikator` varchar(200) NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `indikator_ruangan`
--

INSERT INTO `indikator_ruangan` (`id`, `ruangan_id`, `nama_indikator`, `urutan`) VALUES
(1, 1, 'Lantai Bersih', 1),
(2, 1, 'Kursi/Tamu Rapi', 2),
(3, 1, 'Meja Resepsionis Bersih', 3),
(4, 1, 'Tempat Sampah Kosong', 4),
(5, 1, 'Area Kaca/Pintu Bersih', 5),
(6, 2, 'Kebersihan Lantai', 1),
(7, 2, 'Kebersihan Meja', 2),
(8, 2, 'Kebersihan Wastafel', 3),
(9, 2, 'Tempat Sampah', 4),
(10, 2, 'Peralatan Pantry', 5),
(11, 3, 'Lantai bersih', 1),
(12, 3, 'Meja kerja rapi', 2),
(13, 3, 'Kursi tertata', 3),
(14, 3, 'Sampah telah dibuang', 4),
(15, 3, 'Peralatan kantor tertata', 5),
(16, 3, 'AC/Kipas berfungsi', 6),
(17, 3, 'Lampu berfungsi', 7),
(18, 3, 'Jendela dan pintu bersih', 8),
(19, 3, 'Dokumen tersimpan rapi', 9),
(20, 3, 'Area kerja bebas bau/tumpahan', 10),
(26, 4, 'Kebersihan Lantai', 1),
(27, 4, 'Kursi & Meja Rapi', 2),
(28, 4, 'AC/Fan Berfungsi', 3),
(29, 4, 'Peralatan Presentasi', 4),
(33, 5, 'Kondisi Lantai', 1),
(34, 5, 'Kebersihan Meja', 2),
(35, 5, 'Kebersihan Kursi', 3),
(36, 5, 'Tempat Sampah', 4),
(37, 5, 'Kerapian Ruangan', 5),
(40, 6, 'Lantai toilet bersih dan kering', 1),
(41, 6, 'Kloset bersih', 2),
(42, 6, 'Wastafel bersih', 3),
(43, 6, 'Cermin bersih', 4),
(44, 6, 'Tempat sampah dikosongkan', 5),
(45, 6, 'Sabun cuci tangan tersedia', 6),
(46, 6, 'Tisu toilet dan towel tersedia', 7),
(47, 6, 'Tidak ada bau menyengat', 8),
(48, 6, 'Saluran air lancar', 9),
(49, 6, 'Pintu dan dinding bersih', 10),
(50, 6, 'Kondisi ruangan dan langit-langit bebas debu dan sarang laba-laba', 11);

-- --------------------------------------------------------

--
-- Table structure for table `ruangan`
--

CREATE TABLE `ruangan` (
  `id` int(11) NOT NULL,
  `kode_qr` varchar(50) NOT NULL,
  `nama_ruangan` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ruangan`
--

INSERT INTO `ruangan` (`id`, `kode_qr`, `nama_ruangan`, `created_at`) VALUES
(1, 'LOBBY-01', 'Ruang Lobby BNNK Batang', '2026-11-18 01:23:49'),
(2, 'PANTRY-01', 'Pantry BNNK Batang', '2026-11-18 01:23:49'),
(3, 'KERJA-01', 'Ruang Kerja BNNK Batang', '2026-11-18 01:23:49'),
(4, 'RAPAT-01', 'Ruang Rapat BNNK Batang', '2026-11-18 01:23:49'),
(5, 'TAMU-01', 'Ruang Tamu Tengah BNNK Batang', '2026-11-18 01:23:49'),
(6, 'TOILET-02', 'Toilet Lt. 2 Kantor BNNK Batang', '2026-11-18 01:23:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('petugas','pj_sarpras','kasubbag_umum','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'Petugas Kebersihan', 'petugas', '$2y$12$wWU99p/D1uDq1GEdoNfsQ.gtbSA97oX8O.tm1vg5U9oBoEQXqT7RS', 'petugas', '2026-11-18 01:23:49'),
(2, 'PJ Sarpras', 'pj_sarpras', '$2y$12$4FrB8gQapK2Z4pTMTT.AaOp3xCMw3goAsSfRcEfWGeYCw/hhY3zuO', 'pj_sarpras', '2026-11-18 01:23:49'),
(3, 'Kasubbag Umum', 'kasubbag', '$2y$12$wWU99p/D1uDq1GEdoNfsQ.gtbSA97oX8O.tm1vg5U9oBoEQXqT7RS', 'kasubbag_umum', '2026-11-18 01:23:49'),
(4, 'Administrator', 'admin', '$2y$12$wWU99p/D1uDq1GEdoNfsQ.gtbSA97oX8O.tm1vg5U9oBoEQXqT7RS', 'admin', '2026-11-18 01:23:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ceklist_detail`
--
ALTER TABLE `ceklist_detail`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_detail_cek` (`ceklist_harian_id`,`jam_cek`,`indikator_id`),
  ADD KEY `fk_detail_indikator` (`indikator_id`),
  ADD KEY `fk_detail_petugas` (`petugas_id`);

--
-- Indexes for table `ceklist_harian`
--
ALTER TABLE `ceklist_harian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_ceklist_harian` (`ruangan_id`,`tanggal`);

--
-- Indexes for table `indikator_ruangan`
--
ALTER TABLE `indikator_ruangan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_indikator_ruangan` (`ruangan_id`);

--
-- Indexes for table `ruangan`
--
ALTER TABLE `ruangan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_qr` (`kode_qr`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ceklist_detail`
--
ALTER TABLE `ceklist_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `ceklist_harian`
--
ALTER TABLE `ceklist_harian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `indikator_ruangan`
--
ALTER TABLE `indikator_ruangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `ruangan`
--
ALTER TABLE `ruangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ceklist_detail`
--
ALTER TABLE `ceklist_detail`
  ADD CONSTRAINT `fk_detail_harian` FOREIGN KEY (`ceklist_harian_id`) REFERENCES `ceklist_harian` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detail_indikator` FOREIGN KEY (`indikator_id`) REFERENCES `indikator_ruangan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detail_petugas` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `ceklist_harian`
--
ALTER TABLE `ceklist_harian`
  ADD CONSTRAINT `fk_ceklist_ruangan` FOREIGN KEY (`ruangan_id`) REFERENCES `ruangan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `indikator_ruangan`
--
ALTER TABLE `indikator_ruangan`
  ADD CONSTRAINT `fk_indikator_ruangan` FOREIGN KEY (`ruangan_id`) REFERENCES `ruangan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
