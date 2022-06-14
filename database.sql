SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
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

CREATE TABLE `locked` (
  `id` varchar(10) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `vid_key` varchar(512) DEFAULT NULL,
  `allowed_user` varchar(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `login_log` (
  `log_id` int(11) NOT NULL,
  `id` varchar(10) NOT NULL,
  `ip_address` varchar(20) NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `videos` (
  `file_loc` varchar(200) NOT NULL,
  `id` varchar(10) NOT NULL,
  `views` int(10) NOT NULL DEFAULT 0,
  `last_checked` datetime NOT NULL DEFAULT current_timestamp(),
  `credit_time` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;


ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `hourly_view`
  ADD PRIMARY KEY (`id`,`date`,`hour`),
  ADD KEY `date` (`date`);

ALTER TABLE `locked`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `login_log`
  ADD PRIMARY KEY (`log_id`);

ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `last_checked` (`last_checked`),
  ADD KEY `views` (`views`);


ALTER TABLE `login_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `hourly_view`
  ADD CONSTRAINT `hourly_view_ibfk_1` FOREIGN KEY (`id`) REFERENCES `videos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;
