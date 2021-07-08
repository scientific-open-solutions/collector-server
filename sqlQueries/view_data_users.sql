-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 09, 2021 at 12:02 AM
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
-- Database: `anthony_ocollector`
--

-- --------------------------------------------------------

--
-- Structure for view `view_data_users`
--

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
) ;

--
-- VIEW  `view_data_users`
-- Data: None
--

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
