-- =====================================================
-- DATABASE: absensi
-- Dibuat otomatis oleh ChatGPT untuk sistem absensi kelas
-- =====================================================

-- 1️⃣ Buat database baru
CREATE DATABASE IF NOT EXISTS absensi;
USE absensi;

-- 2️⃣ Tabel user
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'kelas') NOT NULL
);

-- Isi data user awal
INSERT INTO `user` (`username`, `password`, `role`) VALUES
('admin', 'password', 'admin'),
('XII_RPL', '12345', 'kelas'),
('XI_RPL', '12345', 'kelas');

-- 3️⃣ Tabel siswa
DROP TABLE IF EXISTS `siswa`;
CREATE TABLE `siswa` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nis` VARCHAR(20) NOT NULL,
    `nama` VARCHAR(100) NOT NULL,
    `kelas` VARCHAR(50) NOT NULL
);

-- Isi data siswa awal
INSERT INTO `siswa` (`nis`, `nama`, `kelas`) VALUES
('001', 'Delvin Yau', 'XII_RPL'),
('002', 'Unyah', 'XII_RPL'),
('003', 'Kanbe Daisuke', 'XI_RPL');

-- 4️⃣ Tabel absensi
DROP TABLE IF EXISTS `absensi`;
CREATE TABLE `absensi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `siswa_id` INT NOT NULL,
    `tanggal` DATE NOT NULL,
    `keterangan` ENUM('Hadir', 'Izin', 'Sakit', 'Alfa') DEFAULT 'Hadir',
    FOREIGN KEY (`siswa_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE
);

-- 5️⃣ Optional: contoh data absensi (bisa kamu hapus kalau mau kosong)
INSERT INTO `absensi` (`siswa_id`, `tanggal`, `keterangan`) VALUES
(1, '2025-10-27', 'Hadir'),
(2, '2025-10-27', 'Izin'),
(3, '2025-10-27', 'Sakit');

-- =====================================================
-- ✅ Database absensi siap digunakan
-- =====================================================
