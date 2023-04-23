-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Gegenereerd op: 23 apr 2023 om 14:53
-- Serverversie: 10.4.27-MariaDB
-- PHP-versie: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `catering`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `facility`
--

CREATE TABLE `facility` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `location_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `facility`
--

INSERT INTO `facility` (`id`, `name`, `location_id`, `created_at`) VALUES
(1, 'Wedding Catering', 2, '2023-04-23 11:40:35'),
(2, 'Bedrijfscatering', 1, '2023-04-23 11:41:42'),
(3, 'BBQ Catering', 2, '2023-04-23 11:43:14');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `facility_tags`
--

CREATE TABLE `facility_tags` (
  `facility_id` int(11) DEFAULT NULL,
  `tag_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `facility_tags`
--

INSERT INTO `facility_tags` (`facility_id`, `tag_id`) VALUES
(2, 28),
(2, 29),
(2, 25),
(3, 30),
(3, 27),
(3, 31),
(3, 32),
(1, 25),
(1, 28),
(1, 33);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `location`
--

CREATE TABLE `location` (
  `id` int(11) NOT NULL,
  `city` varchar(50) NOT NULL,
  `address` varchar(200) NOT NULL,
  `zip_code` varchar(10) NOT NULL,
  `country_code` varchar(10) NOT NULL,
  `phone_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `location`
--

INSERT INTO `location` (`id`, `city`, `address`, `zip_code`, `country_code`, `phone_number`) VALUES
(1, 'Amsterdam', 'Test straat 14', '1056AD', 'NL', '+31 612345678'),
(2, 'London', 'Test straat 2', '3412ED', 'UK', '+44 163602922'),
(3, 'Utrecht', 'Test straat 90', '2106PO', 'NL', '+31 612345644');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `tag`
--

CREATE TABLE `tag` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `tag`
--

INSERT INTO `tag` (`id`, `name`) VALUES
(34, ''),
(30, 'BBQ'),
(32, 'Koude dranken'),
(33, 'Patat'),
(24, 'Pizza'),
(31, 'Salades'),
(28, 'Sandwiches'),
(25, 'Soep'),
(23, 'Taart'),
(27, 'Tapas'),
(26, 'Vegetarisch'),
(29, 'Warme dranken');

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `facility`
--
ALTER TABLE `facility`
  ADD PRIMARY KEY (`id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexen voor tabel `facility_tags`
--
ALTER TABLE `facility_tags`
  ADD KEY `facility_tags_ibfk_1` (`facility_id`),
  ADD KEY `facility_tags_ibfk_2` (`tag_id`);

--
-- Indexen voor tabel `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `tag`
--
ALTER TABLE `tag`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `facility`
--
ALTER TABLE `facility`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT voor een tabel `location`
--
ALTER TABLE `location`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT voor een tabel `tag`
--
ALTER TABLE `tag`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `facility`
--
ALTER TABLE `facility`
  ADD CONSTRAINT `facility_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `location` (`id`);

--
-- Beperkingen voor tabel `facility_tags`
--
ALTER TABLE `facility_tags`
  ADD CONSTRAINT `facility_tags_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facility` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `facility_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
