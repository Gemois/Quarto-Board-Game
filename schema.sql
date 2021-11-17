-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 17, 2021 at 08:50 PM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 8.0.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `adise21_quarto_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `clean_board` ()  BEGIN
REPLACE INTO board SELECT * FROM board_empty;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `place_piece` (`X` TINYINT(1), `Y` TINYINT(1), `piece_id` INT)  BEGIN
UPDATE board   
SET piece =piece_id WHERE x=X AND y=Y;
UPDATE pieces 
SET available=false WHERE pieces_id=piece_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `board`
--

CREATE TABLE `board` (
  `x` tinyint(1) NOT NULL,
  `y` tinyint(1) NOT NULL,
  `piece` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `board_empty`
--

CREATE TABLE `board_empty` (
  `x` tinyint(1) NOT NULL,
  `y` tinyint(1) NOT NULL,
  `piece` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `game_status`
--

CREATE TABLE `game_status` (
  `status` enum('not active','initialized','started','\nended','aborded') NOT NULL DEFAULT 'not active',
  `p_turn` int(11) DEFAULT NULL,
  `result` enum('B','W','D') DEFAULT NULL,
  `last_change` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pieces`
--

CREATE TABLE `pieces` (
  `pieces_id` int(11) NOT NULL,
  `is_light` tinyint(1) NOT NULL,
  `is_round` tinyint(1) NOT NULL,
  `is_short` tinyint(1) NOT NULL,
  `is_solid` tinyint(1) NOT NULL,
  `available` tinyint(1) DEFAULT 1,
  `last_action` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `player_id` int(11) NOT NULL,
  `username` varchar(20) DEFAULT NULL,
  `password` varchar(10) NOT NULL,
  `token` varchar(100) DEFAULT NULL,
  `last_action` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE `session` (
  `session_id` int(11) NOT NULL,
  `player1_id` int(11) DEFAULT NULL,
  `player2_id` int(11) DEFAULT NULL,
  `player_turn` varchar(100) DEFAULT NULL,
  `last_action` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `board`
--
ALTER TABLE `board`
  ADD PRIMARY KEY (`x`,`y`),
  ADD KEY `piece` (`piece`);

--
-- Indexes for table `board_empty`
--
ALTER TABLE `board_empty`
  ADD PRIMARY KEY (`x`,`y`),
  ADD KEY `piece` (`piece`);

--
-- Indexes for table `game_status`
--
ALTER TABLE `game_status`
  ADD KEY `p_turn` (`p_turn`);

--
-- Indexes for table `pieces`
--
ALTER TABLE `pieces`
  ADD PRIMARY KEY (`pieces_id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`player_id`);

--
-- Indexes for table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `player1_id` (`player1_id`),
  ADD KEY `player2_id` (`player2_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pieces`
--
ALTER TABLE `pieces`
  MODIFY `pieces_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `player_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `session`
--
ALTER TABLE `session`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `board`
--
ALTER TABLE `board`
  ADD CONSTRAINT `board_ibfk_1` FOREIGN KEY (`piece`) REFERENCES `pieces` (`pieces_id`);

--
-- Constraints for table `board_empty`
--
ALTER TABLE `board_empty`
  ADD CONSTRAINT `board_empty_ibfk_1` FOREIGN KEY (`piece`) REFERENCES `pieces` (`pieces_id`);

--
-- Constraints for table `game_status`
--
ALTER TABLE `game_status`
  ADD CONSTRAINT `game_status_ibfk_1` FOREIGN KEY (`p_turn`) REFERENCES `players` (`player_id`);

--
-- Constraints for table `session`
--
ALTER TABLE `session`
  ADD CONSTRAINT `session_ibfk_1` FOREIGN KEY (`player1_id`) REFERENCES `players` (`player_id`),
  ADD CONSTRAINT `session_ibfk_2` FOREIGN KEY (`player2_id`) REFERENCES `players` (`player_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
