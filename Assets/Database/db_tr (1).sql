-- Host: 127.0.0.1
-- Waktu pembuatan: 08 Des 2025 pada 18.01
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
-- Database: `db_tr`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `poster_url` text NOT NULL,
  `rating` varchar(10) DEFAULT 'N/A',
  `imdbID` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `favorites`
--

INSERT INTO `favorites` (`id`, `title`, `poster_url`, `rating`, `imdbID`, `created_at`) VALUES
(1, 'Sha Ma Te, Wo Ai Ni (We Were Smart)', 'https://m.media-amazon.com/images/M/MV5BOTg5YWZhODEtYWJiZC00MDFjLWFmN2QtMTc5NjI3Njc5YzYyXkEyXkFqcGc@._V1_SX300.jpg', '8.0', 'tt13809752', '2025-11-24 04:18:49'),
(3, 'Home Alone', 'https://m.media-amazon.com/images/M/MV5BNzNmNmQ2ZDEtMTc1MS00NjNiLThlMGUtZmQxNTg1Nzg5NWMzXkEyXkFqcGc@._V1_SX300.jpg', '8.0', 'tt0099785', '2025-11-24 05:58:53'),
(5, 'Almost Human', 'https://m.media-amazon.com/images/M/MV5BMzQ1NDQ3MjUxOF5BMl5BanBnXkFtZTgwMTY2MDczMDE@._V1_SX300.jpg', '8.0', 'tt2654580', '2025-11-24 06:01:17'),
(6, 'The Human Centipede 2 (Full Sequence)', 'https://m.media-amazon.com/images/M/MV5BMjkwMDI0NjA5OV5BMl5BanBnXkFtZTcwODAxODI4Ng@@._V1_SX300.jpg', '8.0', 'tt1530509', '2025-11-24 06:01:21'),
(7, 'The Human Stain', 'https://m.media-amazon.com/images/M/MV5BMTk5MjQyNTcxNV5BMl5BanBnXkFtZTcwMjcwNDAwMQ@@._V1_SX300.jpg', '8.0', 'tt0308383', '2025-11-24 06:01:23'),
(10, 'Bila Esok Ibu Tiada', 'https://m.media-amazon.com/images/M/MV5BNGNjNDUwZjMtODI0Ny00ODY3LTkyODEtZDY0YTZjNjVjMmE3XkEyXkFqcGc@._V1_SX300.jpg', '8.0', 'tt31079741', '2025-11-24 06:07:49'),
(13, '1 Kakak 7 Ponakan', 'https://m.media-amazon.com/images/M/MV5BYWI0ZmNiZmEtYjdhZC00YjA0LWFjNDktZDQwMDczYjk2YTVlXkEyXkFqcGc@._V1_SX300.jpg', '8.0', 'tt32881480', '2025-11-24 06:11:11'),
(14, 'Sore: Wife from the Future', 'https://m.media-amazon.com/images/M/MV5BMmExZTcyZGUtN2Q4NC00NmFiLWI1NmQtOTg3OWRlMmE3OGVjXkEyXkFqcGc@._V1_SX300.jpg', '9.0', 'tt34548722', '2025-11-24 08:51:19'),
(15, 'Kukira Kau Rumah', 'https://m.media-amazon.com/images/M/MV5BZTliYWZhZjYtZTVjNi00NmJiLTgxNzItZTc1Njg4MzE0YzQ2XkEyXkFqcGc@._V1_SX300.jpg', '8.0', 'tt12351994', '2025-12-02 02:54:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `film`
--

CREATE TABLE `film` (
  `judul_film` varchar(255) NOT NULL,
  `genre` varchar(255) NOT NULL,
  `durasi` varchar(255) NOT NULL,
  `rating` varchar(255) NOT NULL,
  `gambar` text NOT NULL,
  `saran_umur` varchar(255) NOT NULL,
  `tanggal_rilis` date NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `sinopsis` text NOT NULL,
  `id_film` int(11) NOT NULL,
  `stok` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `film`
--

INSERT INTO `film` (`judul_film`, `genre`, `durasi`, `rating`, `gambar`, `saran_umur`, `tanggal_rilis`, `harga`, `sinopsis`, `id_film`, `stok`) VALUES
('Ngeri-Ngeri Sedap', 'Komedi', '1h20m', '9.5/10', 'img/NgeriSedap.jpeg', '18+', '2025-12-01', 30.00, 'Seru', 1, 0),
('Agak Laen', 'Komedi Horor', '119 menit', '9.8/10', 'img\\Agak_Laen.jpg', '16+', '2025-11-04', 35.00, 'Lucu', 2, 0),
('Jumbo 2025', 'Animasi Anak', '139 menit', '9,0/10', '../../img/Jumbo.jpg', 'RBO', '2024-02-12', 35000.00, 'Asik', 3, 40);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal`
--

CREATE TABLE `jadwal` (
  `id_jadwal` int(11) NOT NULL,
  `id_film` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `studio` varchar(50) DEFAULT 'Studio 1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jadwal`
--

INSERT INTO `jadwal` (`id_jadwal`, `id_film`, `tanggal`, `jam_mulai`, `studio`) VALUES
(1, 1, '2025-12-07', '12:30:00', 'Studio 1'),
(2, 1, '2025-12-07', '15:00:00', 'Studio 1'),
(3, 1, '2025-12-08', '13:00:00', 'Studio 2'),
(4, 1, '2025-12-09', '14:00:00', 'Studio 1'),
(5, 2, '2025-12-07', '16:00:00', 'Studio 3'),
(6, 2, '2025-12-08', '19:00:00', 'Studio 2'),
(7, 3, '2025-02-21', '02:11:00', 'Studio Luar');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_film` int(11) NOT NULL,
  `tanggal_pesan` datetime NOT NULL DEFAULT current_timestamp(),
  `tanggal_tayang` date NOT NULL,
  `jam_tayang` varchar(10) NOT NULL,
  `jumlah_tiket` int(11) NOT NULL,
  `kursi` varchar(255) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `status` enum('pending','terverifikasi','batal') NOT NULL DEFAULT 'pending',
  `id_kasir` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_user`, `id_film`, `tanggal_pesan`, `tanggal_tayang`, `jam_tayang`, `jumlah_tiket`, `kursi`, `total_harga`, `status`, `id_kasir`) VALUES
(1, 8, 1, '2025-12-05 18:55:32', '2025-12-06', '13:00', 1, 'B1', 30.00, 'terverifikasi', 11),
(2, 8, 2, '2025-12-07 15:29:48', '2025-12-07', '16:00', 1, 'A1', 35.00, 'terverifikasi', 11),
(4, 14, 1, '2025-12-08 01:06:56', '2025-12-09', '14:00', 1, 'D2', 30.00, 'terverifikasi', 11),
(5, 8, 2, '2025-12-08 13:09:12', '2025-12-08', '19:00', 1, 'D2', 35.00, 'pending', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user','kasir') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `created_at`) VALUES
(4, 'talita', '$2y$10$ErrR06l9bkgTQ.lN0cwGuOZ2/cYs6B0MVBpgqW.uxReetaqz4IRYm', 'user', '2025-12-02 03:13:52'),
(5, 'user123', '$2y$10$ef4byQTMyByIs6luCFex..fMFrBVds2xQ3ySbTmqyaKmNxmcr/15S', 'user', '2025-12-05 08:53:32'),
(6, 'briangoo', '$2y$10$g4qF6cWbOzQvK9kOkWJYLe9HGfWq7oDYsB838dZ/nZG0pwRC7jfv.', 'user', '2025-12-05 09:39:18'),
(7, 'ww', '$2y$10$UebwT1zofblNqypiCnj1cuNyxZiLIRLu4WW5mhxXUgv9scQKGDCzS', 'user', '2025-12-05 09:40:30'),
(8, 'talitaq', 'qq', 'user', '2025-12-05 09:47:45'),
(10, 'admin12', 'admin123', 'admin', '2025-12-07 08:16:32'),
(11, 'kasir12', '222', 'kasir', '2025-12-07 08:30:37'),
(13, 'benri', '672024259', 'admin', '2025-12-07 18:04:09'),
(14, 'BenriUser', '123', 'user', '2025-12-07 18:06:28'),
(15, 'kasir', 'kasir123', 'kasir', '2025-12-08 17:01:32');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `imdbID` (`imdbID`);

--
-- Indeks untuk tabel `film`
--
ALTER TABLE `film`
  ADD PRIMARY KEY (`id_film`);

--
-- Indeks untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_film` (`id_film`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `fk_user_transaksi` (`id_user`),
  ADD KEY `fk_film_transaksi` (`id_film`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `film`
--
ALTER TABLE `film`
  MODIFY `id_film` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`id_film`) REFERENCES `film` (`id_film`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_film_transaksi` FOREIGN KEY (`id_film`) REFERENCES `film` (`id_film`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_transaksi` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
