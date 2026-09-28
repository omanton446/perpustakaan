-- phpMyAdmin SQL Dump
-- versi 2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `perpus` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `perpus`;

-- Tabel users
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `peran` enum('admin','peminjam') NOT NULL DEFAULT 'peminjam',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel buku
CREATE TABLE `buku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `pengarang` varchar(255) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,  -- bisa URL atau nama file hasil upload
  `stok` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel peminjaman
CREATE TABLE `peminjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `buku_id` int(11) NOT NULL,
  `nama_peminjam` varchar(255) NOT NULL,
  `tgl_pinjam` date NOT NULL,
  `tgl_kembali` date NOT NULL,
  `jumlah_pinjam` int(11) DEFAULT 1,
  `status` varchar(50) DEFAULT 'Dipinjam',
  `kondisi` enum('baik','rusak') DEFAULT NULL,
  `denda` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `buku_id` (`buku_id`),
  CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data awal
INSERT INTO `users` (`id`, `username`, `email`, `password`, `peran`) VALUES
(1, 'admin', 'admin@mylibrary.com', '$2y$10$0cUW0TETtB.dvjKWSCtcm.s5Dr7uv3PXQzAmSqxmYHwIXqWGCYXxW', 'admin'),  -- password: admin123
(2, 'peminjam', 'peminjam@mylibrary.com', '$2y$10$sB.MBmx/svg5.k6VfGTice/xQdvyiDC1Mz5qr.sWgINiEngkNkQRm', 'peminjam'); -- password: peminjam123

INSERT INTO `buku` (`id`, `judul`, `pengarang`, `kategori`, `deskripsi`, `gambar`, `stok`) VALUES
(1, 'Sangkuriang', 'Yuliadi Soekardi', 'Legenda', 'Kisah Sangkuriang dan Dayang Sumbi', 'sangkuriang.jpg', 5),
(2, 'Legenda Bukit Perak', 'Ricky A. Manik', 'Fiksi', 'Datuk Sengalo dengan keris perak sakti', 'bukit_perak.jpg', 3);

COMMIT;