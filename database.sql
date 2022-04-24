SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `1227cloud` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `1227cloud`;

CREATE TABLE `admin` (
  `id` varchar(10) NOT NULL,
  `pw` varchar(64) NOT NULL,
  `name` varchar(10) NOT NULL,
  `type` varchar(5) NOT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `hourly_view` (
  `id` varchar(10) NOT NULL,
  `date` date NOT NULL,
  `hour` tinyint(4) NOT NULL,
  `views` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `videos` (
  `file_loc` varchar(200) NOT NULL,
  `id` varchar(10) NOT NULL,
  `views` int(10) NOT NULL DEFAULT 0,
  `last_checked` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;


ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `hourly_view`
  ADD PRIMARY KEY (`id`,`date`,`hour`),
  ADD KEY `date` (`date`);

ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `last_checked` (`last_checked`),
  ADD KEY `views` (`views`);


ALTER TABLE `hourly_view`
  ADD CONSTRAINT `hourly_view_ibfk_1` FOREIGN KEY (`id`) REFERENCES `videos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
