-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 13 Jun 2025 pada 16.34
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
-- Database: `ip-call`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `adzan`
--

CREATE TABLE `adzan` (
  `key` varchar(255) NOT NULL,
  `value` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `adzan`
--

INSERT INTO `adzan` (`key`, `value`) VALUES
('ashar', '14:48:00'),
('dhuhur', '11:27:00'),
('isya', '21:12:00'),
('maghrib', '17:18:00'),
('subuh', '04:12:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `bed`
--

CREATE TABLE `bed` (
  `id` varchar(255) NOT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `vol` int(11) NOT NULL DEFAULT 100,
  `mic` int(11) NOT NULL DEFAULT 100,
  `tw` int(11) NOT NULL DEFAULT 1,
  `mode` int(11) NOT NULL DEFAULT 0,
  `ip` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `bypass` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktur dari tabel `category_history`
--

CREATE TABLE `category_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data untuk tabel `category_history`
--

INSERT INTO `category_history` (`id`, `name`) VALUES
(1, 'PANGGILAN MASUK'),
(2, 'PANGGILAN TIDAK TERJAWAB'),
(3, 'PANGGILAN KELUAR');

-- --------------------------------------------------------

--
-- Struktur dari tabel `category_log`
--

CREATE TABLE `category_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data untuk tabel `category_log`
--

INSERT INTO `category_log` (`id`, `name`) VALUES
(1, 'DARURAT'),
(2, 'TELEPON'),
(3, 'CODE BLUE'),
(4, 'INFUS'),
(5, 'PERAWAT');

-- --------------------------------------------------------

--
-- Struktur dari tabel `history`
--

CREATE TABLE `history` (
  `id` int(10) UNSIGNED NOT NULL,
  `bed_id` varchar(255) NOT NULL,
  `category_history_id` int(11) NOT NULL,
  `duration` varchar(255) DEFAULT NULL,
  `record` varchar(255) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktur dari tabel `list_hour_audio`
--

CREATE TABLE `list_hour_audio` (
  `time` time NOT NULL,
  `vol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `log`
--

CREATE TABLE `log` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_log_id` int(10) UNSIGNED NOT NULL,
  `value` text DEFAULT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `time` bigint(20) DEFAULT NULL,
  `nurse_presence` tinyint(1) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktur dari tabel `mastersound`
--

CREATE TABLE `mastersound` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `source` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mastersound`
--

INSERT INTO `mastersound` (`id`, `name`, `source`) VALUES
(1, 'Ruang', 'static/ruang.mp3'),
(2, 'Kamar', 'static/kamar.mp3'),
(3, 'Toilet', 'static/toilet.mp3'),
(4, 'Bed', 'static/Bed.mp3');

-- --------------------------------------------------------

--
-- Struktur dari tabel `playlist`
--

CREATE TABLE `playlist` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `volume` int(11) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `playlist_item`
--

CREATE TABLE `playlist_item` (
  `id` int(11) NOT NULL,
  `ord` int(11) NOT NULL,
  `path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `room`
--

CREATE TABLE `room` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `running_text` varchar(255) DEFAULT NULL,
  `type_bed` varchar(255) DEFAULT NULL,
  `bed_separator` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `bypass` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktur dari tabel `running_text`
--

CREATE TABLE `running_text` (
  `topic` varchar(255) NOT NULL,
  `speed` int(11) DEFAULT NULL,
  `brightness` int(11) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `toilet`
--

CREATE TABLE `toilet` (
  `id` varchar(255) NOT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `bypass` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2a$12$2EFZP1/0rFCgFCvXQTRn5O9zN1S9wg9T4bOlSKTf3MmIZeA04nuki', 'admin'),
(2, 'user', '$2a$12$EekB/L1fpWNNY0C1YuN0LeJd4p3BZ8TXosvZ42E.abB4imgyRp0BO', 'user'),
(3, 'teknisi', '$2a$12$Rj7/pQzQXohNGdo7EbBZGuOs1eh2Y5f3EhqfXvRy3CtUEtkHjH6PS', 'teknisi');

-- --------------------------------------------------------

--
-- Struktur dari tabel `utils`
--

CREATE TABLE `utils` (
  `type` varchar(255) NOT NULL,
  `value` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `utils`
--

INSERT INTO `utils` (`type`, `value`) VALUES
('interval_update_status', 35000),
('one_room_one_device', 0),
('interval_speaks', 8000),
('timeout_call', 60000),
('time_autorefresh', 0),
('timeout_running_text', 8500),
('timeout_time_activity', 60000),
('adzan_volume', 10),
('adzan_auto', 0),
('adzan_latitude', -7.288354699999995),
('adzan_longitude', 112.72549628465647),
('adzan_active', 1);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `adzan`
--
ALTER TABLE `adzan`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `bed`
--
ALTER TABLE `bed`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `category_history`
--
ALTER TABLE `category_history`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `category_log`
--
ALTER TABLE `category_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `mastersound`
--
ALTER TABLE `mastersound`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mastersound_pk_2` (`name`);

--
-- Indeks untuk tabel `playlist`
--
ALTER TABLE `playlist`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `playlist_item`
--
ALTER TABLE `playlist_item`
  ADD PRIMARY KEY (`id`,`ord`);

--
-- Indeks untuk tabel `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `running_text`
--
ALTER TABLE `running_text`
  ADD PRIMARY KEY (`topic`);

--
-- Indeks untuk tabel `toilet`
--
ALTER TABLE `toilet`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `category_log`
--
ALTER TABLE `category_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `history`
--
ALTER TABLE `history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `log`
--
ALTER TABLE `log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `mastersound`
--
ALTER TABLE `mastersound`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `playlist`
--
ALTER TABLE `playlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
