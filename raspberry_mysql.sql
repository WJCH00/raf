-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Czas generowania: 16 Sty 2015, 19:17
-- Wersja serwera: 5.6.21
-- Wersja PHP: 5.6.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Baza danych: `raspberry_mysql`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `leds`
--

CREATE TABLE IF NOT EXISTS `leds` (
  `id` int(11) NOT NULL,
  `on` tinyint(1) NOT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Zrzut danych tabeli `leds`
--

INSERT INTO `leds` (`id`, `wlacz`) VALUES
(1, 0),
(2, 0),
(3, 0),
(4, 1),
(5, 0);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `distance`
--

CREATE TABLE IF NOT EXISTS `distance` (
`id` int(11) NOT NULL,
  `distance` text NOT NULL,
  `date` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `temp_hum`
--

CREATE TABLE IF NOT EXISTS `temp_hum` (
`id` int(11) NOT NULL,
  `temp` float NOT NULL,
  `humidity` float NOT NULL,
  `date` text NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Zrzut danych tabeli `temp_hum`
--

INSERT INTO `temp_hum` (`id`, `temp`, `humidity`, `date`) VALUES
(1, 20.5, 0.3, '2015-01-16');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indexes for table `leds`
--
ALTER TABLE `leds`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `distance`
--
ALTER TABLE `distance`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `temp_hum`
--
ALTER TABLE `temp_hum`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT dla tabeli `distance`
--
ALTER TABLE `distance`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `temp_hum`
--
ALTER TABLE `temp_hum`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
