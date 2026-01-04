-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 04 Jan 2026 pada 13.24
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
-- Database: `kasir`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gambar` varchar(100) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `status` varchar(10) DEFAULT 'nonaktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `email`, `username`, `password`, `gambar`, `reset_token`, `reset_expiry`, `status`) VALUES
(1, 'reifanevandra8@gmail.com', 'Reifan Evandra', '$2y$10$iW0Mf1GcbV74VBkRBQWWqulNT.qTwqgAnchfNK2Q2ih.t2eVsJ4Hq', 'admin_20250724_111338.jpg', NULL, NULL, 'Aktif'),
(10, 'reyhansaputra@gmail.com', 'Reyhan Saputra', '$2y$10$6L2pc2r3dKonhHU.x046QeoQD.igPGlY88RCYNN.IsUxeeOF/p33K', 'admin_20250731_104537.jpg', NULL, NULL, 'Nonaktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL,
  `fid_transaksi` int(11) DEFAULT NULL,
  `fid_produk` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `subtotal` int(11) DEFAULT NULL,
  `jumlah` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `fid_transaksi`, `fid_produk`, `qty`, `harga`, `subtotal`, `jumlah`) VALUES
(15, 16, 1, 1, 799999, 799999, 0),
(16, 17, 1, 1, 799999, 799999, 0),
(17, 18, 1, 4, 799999, 3199996, 0),
(18, 19, 1, 1, 799999, 799999, 0),
(19, 20, 1, 1, 799999, 799999, 0),
(20, 21, 1, 1, 799999, 799999, 0),
(21, 22, 1, 1, 799999, 799999, 0),
(23, 24, 6, 1, 1111111, 1111111, 0),
(24, 25, 7, 1, 90000, 90000, 0),
(25, 26, 7, 2, 90000, 180000, 0),
(26, 27, 6, 1, 1111111, 1111111, 0),
(27, 28, 6, 1, 1111111, 1111111, 0),
(28, 29, 1, 1, 799999, 799999, 0),
(29, 30, 1, 1, 799999, 799999, 0),
(30, 31, 7, 1, 90000, 90000, 0),
(31, 32, 1, 1, 799999, 799999, 0),
(32, 33, 1, 1, 799999, 799999, 0),
(33, 33, 6, 1, 1111111, 1111111, 0),
(34, 34, 6, 1, 1111111, 1111111, 0),
(35, 35, 6, 1, 1111111, 1111111, 0),
(36, 36, 6, 1, 1111111, 1111111, 0),
(37, 37, 6, 1, 1111111, 1111111, 0),
(38, 38, 6, 1, 1111111, 1111111, 0),
(39, 39, 6, 1, 1111111, 1111111, 0),
(40, 40, 6, 1, 1111111, 1111111, 0),
(41, 41, 1, 1, 799999, 799999, 0),
(42, 42, 1, 1, 799999, 799999, 0),
(43, 43, 1, 1, 799999, 799999, 0),
(44, 44, 1, 1, 799999, 799999, 0),
(45, 45, 1, 1, 799999, 799999, 0),
(46, 45, 7, 1, 90000, 90000, 0),
(47, 46, 1, 1, 799999, 799999, 0),
(48, 47, 1, 1, 799999, 799999, 0),
(49, 48, 1, 1, 799999, 799999, 0),
(50, 49, 7, 1, 90000, 90000, 0),
(51, 51, 7, 1, 90000, 90000, 1),
(52, 53, 7, 1, 90000, 90000, 1),
(53, 54, 7, 1, 90000, 90000, 1),
(54, 55, 7, 1, 90000, 90000, 1),
(55, 56, 7, 1, 90000, 90000, 1),
(56, 57, 7, 1, 90000, 90000, 1),
(57, 58, 7, 1, 90000, 90000, 1),
(58, 59, 7, 1, 90000, 90000, 1),
(59, 60, 7, 9, 90000, 810000, 9),
(60, 61, 7, 1, 1299999, 1299999, 1),
(61, 64, 7, 1, 1299999, 1299999, 1),
(62, 66, 8, 1, 2199999, 2199999, 1),
(63, 67, 7, 1, 1299999, 1299999, 1),
(64, 68, 7, 1, 1299999, 1299999, 1),
(65, 69, 6, 4, 2199999, 8799996, 4),
(66, 70, 6, 2, 2199999, 4399998, 2),
(67, 71, 6, 1, 2199999, 2199999, 1),
(68, 71, 1, 1, 799999, 799999, 1),
(69, 72, 6, 1, 2199999, 2199999, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `tgl_input` date DEFAULT curdate(),
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `tgl_input`, `nama_kategori`) VALUES
(1, '2025-04-07', 'Jam Tangan SmartWatch'),
(2, '2025-04-07', 'Jam Tangan Anak'),
(3, '2025-04-08', 'Jam Tangan Wanita'),
(4, '2025-04-08', 'Jam SmartWatch'),
(5, '2025-04-08', 'Jam Analog'),
(6, '2025-04-08', 'Jam Digital'),
(9, '2025-05-21', 'Jam Fashion');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keranjang`
--

CREATE TABLE `keranjang` (
  `id` int(11) NOT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `nama_produk` varchar(100) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT 1,
  `subtotal` int(11) DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `member`
--

CREATE TABLE `member` (
  `id_member` int(11) NOT NULL,
  `nama_member` varchar(255) NOT NULL,
  `no_telp` varchar(15) NOT NULL,
  `status` enum('aktif','tidak aktif') NOT NULL,
  `poin` int(11) DEFAULT 0,
  `tanggal_aktif` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `member`
--

INSERT INTO `member` (`id_member`, `nama_member`, `no_telp`, `status`, `poin`, `tanggal_aktif`) VALUES
(1, 'kemas', '08571949840', 'tidak aktif', 2329, '2025-05-26 13:51:40'),
(5, 'reyhan', '08568795015', 'tidak aktif', 10, '2025-07-18 14:10:32'),
(6, 'roni', '086543', 'tidak aktif', 27, '2025-05-26 14:16:57'),
(7, 'Fauzan', '076544567', 'tidak aktif', 20, '2025-07-23 21:52:03'),
(8, 'Marsel Herlino', '085719498408', 'tidak aktif', 170, '2025-07-31 10:59:17');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `stok` int(11) NOT NULL,
  `modal` varchar(255) NOT NULL,
  `harga_jual` varchar(255) NOT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `keuntungan` varchar(255) NOT NULL,
  `fid_kategori` int(11) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `deskripsi` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `stok`, `modal`, `harga_jual`, `barcode`, `keuntungan`, `fid_kategori`, `gambar`, `deskripsi`) VALUES
(1, 'Casio G-Shock', 9, '599999', '799999', '1234567890', '200000', 5, 'Casio.jpg', 'Dirancang untuk ketahanan ekstrem, jam tangan G-Shock dikenal tahan terhadap benturan, air, dan kondisi ekstrem lainnya.'),
(6, 'Garmin 635', 2, '1899999', '2199999', '111111', '300000', 1, 'garmin.jpg', 'Untuk pelari biasa hingga menengah, dengan AMOLED display dan analitik pelatihan'),
(7, 'Fossil Minimalist Slim', 10, '899999', '1299999', '535246', '400000', 3, 'fossil.jpg', ' favorit minimalist dengan harga terjangkau sekitar $120'),
(8, 'Samsung Galaxy Watch', 10, '1799999', '2199999', '123456789', '400000', 1, 'galaxy.jpg', 'Cerdas, stylish, fitness tracking, tahan air, layar AMOLED jernih.'),
(9, 'Samsung s9110', 10, '1599999', '1899999', '213452123', '300000', 1, 'samsung.jpg', 'desain elegan, fitur panggilan, layar sentuh, dan koneksi Bluetooth.');

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_poin`
--

CREATE TABLE `riwayat_poin` (
  `id` int(11) NOT NULL,
  `id_member` int(11) DEFAULT NULL,
  `poin` int(11) DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `tgl_pembelian` date DEFAULT NULL,
  `total_harga` int(11) DEFAULT NULL,
  `fid_admin` int(11) DEFAULT NULL,
  `fid_member` int(11) DEFAULT NULL,
  `total_keuntungan` int(11) DEFAULT NULL,
  `fid_produk` int(11) DEFAULT NULL,
  `detail` text DEFAULT NULL,
  `uang_dibayar` int(11) DEFAULT 0,
  `diskon` int(11) DEFAULT 0,
  `total_bayar` int(11) DEFAULT 0,
  `kembalian` int(11) DEFAULT 0,
  `metode_pembayaran` varchar(20) DEFAULT 'Tunai'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `tgl_pembelian`, `total_harga`, `fid_admin`, `fid_member`, `total_keuntungan`, `fid_produk`, `detail`, `uang_dibayar`, `diskon`, `total_bayar`, `kembalian`, `metode_pembayaran`) VALUES
(16, '2025-05-19', 799999, NULL, NULL, NULL, NULL, NULL, 900000, 0, 799999, 100001, 'tunai'),
(17, '2025-05-19', 799999, NULL, NULL, NULL, NULL, NULL, 899999, 0, 799999, 100000, 'tunai'),
(18, '2025-05-19', 3199996, NULL, NULL, NULL, NULL, NULL, 3500000, 0, 3199996, 300004, 'tunai'),
(19, '2025-05-20', 799999, NULL, 1, NULL, NULL, NULL, 720000, 79999, 720000, 0, 'tunai'),
(20, '2025-05-20', 799999, NULL, 1, NULL, NULL, NULL, 720000, 79999, 720000, 0, 'tunai'),
(21, '2025-05-20', 799999, NULL, NULL, NULL, NULL, NULL, 799999, 0, 799999, 0, 'tunai'),
(22, '2025-05-20', 799999, NULL, NULL, NULL, NULL, NULL, 799999, 0, 799999, 0, 'tunai'),
(23, '2025-05-21', 1599998, NULL, NULL, NULL, NULL, NULL, 1599998, 0, 1599998, 0, 'tunai'),
(24, '2025-05-21', 1111111, NULL, NULL, NULL, NULL, NULL, 1111111, 0, 1111111, 0, 'tunai'),
(25, '2025-05-22', 90000, NULL, NULL, NULL, NULL, NULL, 99999, 0, 90000, 9999, 'Tunai'),
(26, '2025-05-22', 180000, NULL, 1, NULL, NULL, NULL, 200000, 18000, 162000, 38000, 'Tunai'),
(27, '2025-05-22', 1111111, NULL, 5, NULL, NULL, NULL, 2000000, 111111, 1000000, 1000000, 'Tunai'),
(28, '2025-05-22', 1111111, NULL, 5, NULL, NULL, NULL, 1200000, 111111, 1000000, 200000, 'Tunai'),
(29, '2025-05-25', 799999, NULL, 5, NULL, NULL, NULL, 800000, 79999, 720000, 80000, 'Tunai'),
(30, '2025-05-25', 799999, NULL, 5, NULL, NULL, NULL, 730000, 79999, 720000, 10000, 'Tunai'),
(31, '2025-05-26', 90000, NULL, 5, NULL, NULL, NULL, 90000, 9000, 81000, 9000, 'Tunai'),
(32, '2025-05-26', 799999, NULL, 5, NULL, NULL, NULL, 730000, 79999, 720000, 10000, 'Tunai'),
(33, '2025-05-26', 1911110, NULL, 5, NULL, NULL, NULL, 2000000, 191111, 1719999, 280001, 'Tunai'),
(34, '2025-05-26', 1111111, NULL, 5, NULL, NULL, NULL, 1000000, 111111, 1000000, 0, 'Tunai'),
(35, '2025-05-26', 1111111, NULL, 5, NULL, NULL, NULL, 1500000, 0, 1111111, 388889, 'Tunai'),
(36, '2025-05-26', 1111111, NULL, 5, NULL, NULL, NULL, 1200000, 10000, 1101111, 98889, 'Tunai'),
(37, '2025-05-26', 1111111, NULL, 5, NULL, NULL, NULL, 1100000, 20000, 1091111, 8889, 'Tunai'),
(38, '2025-05-26', 1111111, NULL, 6, NULL, NULL, NULL, 1200000, 0, 1111111, 88889, 'Tunai'),
(39, '2025-05-26', 1111111, NULL, 6, NULL, NULL, NULL, 1200000, 11000, 1100111, 99889, 'Tunai'),
(40, '2025-05-26', 1111111, NULL, 6, NULL, NULL, NULL, 1200000, 5000, 1106111, 93889, 'Tunai'),
(41, '2025-05-26', 799999, NULL, 6, NULL, NULL, NULL, 800000, 21000, 778999, 21001, 'Tunai'),
(42, '2025-05-26', 799999, NULL, 6, NULL, NULL, NULL, 800000, 0, 799999, 1, 'Tunai'),
(43, '2025-05-26', 799999, NULL, 6, NULL, NULL, NULL, 730000, 79999, 720000, 10000, 'Tunai'),
(44, '2025-05-26', 799999, NULL, 5, NULL, NULL, NULL, 790000, 10000, 789999, 1, 'Tunai'),
(45, '2025-05-27', 889999, NULL, 5, NULL, NULL, NULL, 900000, 10000, 879999, 20001, 'Tunai'),
(46, '2025-05-28', 799999, NULL, 5, NULL, NULL, NULL, 790000, 10000, 789999, 1, 'Tunai'),
(47, '2025-06-02', 799999, NULL, NULL, NULL, NULL, NULL, 800000, 0, 799999, 1, 'Tunai'),
(48, '2025-07-18', 799999, NULL, NULL, NULL, NULL, NULL, 900000, 0, 799999, 100001, 'Tunai'),
(49, '2025-07-18', 90000, NULL, NULL, NULL, NULL, NULL, 100000, 0, 90000, 10000, 'Tunai'),
(50, '2025-07-21', 90000, NULL, NULL, NULL, NULL, NULL, 100000, 0, 90000, 10000, 'Tunai'),
(51, '2025-07-21', 90000, NULL, NULL, NULL, NULL, NULL, 100000, 0, 90000, 10000, 'Tunai'),
(52, '2025-07-23', 90000, NULL, 7, NULL, NULL, NULL, 100000, 0, 90000, 10000, 'Tunai'),
(53, '2025-07-23', 90000, NULL, 7, NULL, NULL, NULL, 100000, 0, 90000, 10000, 'Tunai'),
(54, '2025-07-23', 90000, NULL, 7, NULL, NULL, NULL, 100000, 0, 90000, 10000, 'Tunai'),
(55, '2025-07-23', 90000, NULL, 7, NULL, NULL, NULL, 100000, 0, 90000, 10000, 'Tunai'),
(56, '2025-07-23', 90000, NULL, 7, NULL, NULL, NULL, 100000, 0, 90000, 10000, 'Tunai'),
(57, '2025-07-23', 90000, NULL, 7, NULL, NULL, NULL, 100000, 0, 90000, 10000, 'Tunai'),
(58, '2025-07-23', 90000, NULL, 7, NULL, NULL, NULL, 100000, 0, 90000, 10000, 'Tunai'),
(59, '2025-07-23', 90000, NULL, 7, NULL, NULL, NULL, 100000, 0, 90000, 10000, 'Tunai'),
(60, '2025-07-23', 810000, NULL, 7, NULL, NULL, NULL, 900000, 0, 810000, 90000, 'Tunai'),
(61, '2025-07-24', 1299999, NULL, 7, NULL, NULL, NULL, 1300000, 10000, 1289999, 10001, 'Tunai'),
(64, '2025-07-24', 1299999, 1, 7, NULL, NULL, NULL, 1300000, 20000, 1279999, 20001, 'Tunai'),
(66, '2025-07-26', 2199999, 1, 8, NULL, NULL, NULL, 2200000, 0, 2199999, 1, 'Tunai'),
(67, '2025-07-28', 1299999, 1, 8, NULL, NULL, NULL, 1400000, 40000, 1259999, 140001, 'Tunai'),
(68, '2025-07-28', 1299999, 1, 8, NULL, NULL, NULL, 1300000, 20000, 1279999, 20001, 'Tunai'),
(69, '2025-07-31', 8799996, 1, 8, NULL, NULL, NULL, 9000000, 20000, 8779996, 220004, 'Tunai'),
(70, '2026-01-02', 4399998, 1, NULL, NULL, NULL, NULL, 5000000, 0, 4399998, 600002, 'Tunai'),
(71, '2026-01-02', 2999998, 1, NULL, NULL, NULL, NULL, 3000000, 0, 2999998, 2, 'Tunai'),
(72, '2026-01-02', 2199999, 1, NULL, NULL, NULL, NULL, 2199999, 0, 2199999, 0, 'Tunai');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `fid_transaksi` (`fid_transaksi`),
  ADD KEY `detail_transaksi_ibfk_2` (`fid_produk`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`id_member`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indeks untuk tabel `riwayat_poin`
--
ALTER TABLE `riwayat_poin`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `fid_member` (`fid_member`),
  ADD KEY `fid_produk` (`fid_produk`),
  ADD KEY `transaksi_ibfk_1` (`fid_admin`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `member`
--
ALTER TABLE `member`
  MODIFY `id_member` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `riwayat_poin`
--
ALTER TABLE `riwayat_poin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`fid_transaksi`) REFERENCES `transaksi` (`id_transaksi`),
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`fid_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`fid_admin`) REFERENCES `admin` (`id`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`fid_member`) REFERENCES `member` (`id_member`),
  ADD CONSTRAINT `transaksi_ibfk_3` FOREIGN KEY (`fid_produk`) REFERENCES `produk` (`id_produk`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
