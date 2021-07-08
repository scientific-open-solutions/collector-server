-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 07, 2021 at 08:05 PM
-- Server version: 5.7.34
-- PHP Version: 7.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nt906822_collector`
--

-- --------------------------------------------------------

--
-- Table structure for table `contributors`
--

CREATE TABLE `contributors` (
  `project_id` int(13) NOT NULL,
  `user_id` int(13) DEFAULT NULL,
  `contributor_status` varchar(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `data`
--

CREATE TABLE `data` (
  `data_id` int(13) NOT NULL,
  `hashed_user_id` varchar(60) NOT NULL,
  `project_id` int(13) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `filesize` int(13) NOT NULL,
  `trials` int(5) NOT NULL,
  `server_status` varchar(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `incomplete_data`
--

CREATE TABLE `incomplete_data` (
  `data_id` int(20) NOT NULL,
  `trial_no` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(13) NOT NULL,
  `location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(13) NOT NULL PRIMARY KEY,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `hashed_code` varchar(100) NOT NULL,
  `account_status` varchar(1) NOT NULL DEFAULT 'u',
  `salt` varchar(50) DEFAULT NULL,
  `pepper` varchar(50) DEFAULT NULL,
  `expiry_time` int(12) DEFAULT NULL,
  `max_server_space_mb` smallint(6) NOT NULL,
  `max_storage_space_gb` smallint(6) NOT NULL,
  `admin` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE VIEW `view_project_users`  AS  (
  select `proj`.`project_id` AS `project_id`,
  `proj`.`location` AS `location`,
  `ub`.`email` AS `email`,
  `cb`.`contributor_status` AS `contributor_status` from (
    (
      `projects` `proj` join `contributors` `cb` on(
        (`proj`.`project_id` = `cb`.`project_id`)
      )
    ) join `users` `ub` on(
      (`cb`.`user_id` = `ub`.`user_id`)
    )
  )
);


CREATE VIEW `view_data_users`  AS  (
    select `exp`.`project_id` AS `project_id`,
    	`exp`.`location` AS `location`,
    	`ub`.`email` AS `email`,
    	`cb`.`contributor_status` AS `contributor_status`,
    	`data`.`hashed_user_id` AS `hashed_user_id`,
    	`data`.`date` AS `date`,
    	`data`.`filesize` AS `filesize`,
    	`data`.`trials` AS `trials`,
    	`data`.`server_status` AS `server_status`,
    	`data`.`data_id` AS `data_id`,
    	`ub`.`salt` AS `salt`,
    	`ub`.`password` AS `password`,
    	`ub`.`pepper` AS `pepper` 
    from 
    (
        (
            (
                `projects` `exp` join `contributors` `cb` on(
                (`exp`.`project_id` = `cb`.`project_id`)
            )
        ) join `users` `ub` on(
            (`cb`.`user_id` = `ub`.`user_id`)
        )
    ) join `data` on(
        (`data`.`project_id` = `exp`.`project_id`)
    )
    )
);


--
-- Indexes for table `data`
--
ALTER TABLE `data`
  ADD PRIMARY KEY (`data_id`);

--
-- AUTO_INCREMENT for table `data`
--
ALTER TABLE `data`
  MODIFY `data_id` int(13) NOT NULL AUTO_INCREMENT;

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`);




--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(13) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(13) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
