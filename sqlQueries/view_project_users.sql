-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 07, 2021 at 08:01 PM
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
-- Structure for view `view_project_users`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`nt906822`@`localhost` SQL SECURITY DEFINER VIEW `view_project_users`  AS  (select `proj`.`project_id` AS `project_id`,`proj`.`location` AS `location`,`ub`.`email` AS `email`,`cb`.`contributor_status` AS `contributor_status` from ((`projects` `proj` join `contributors` `cb` on((`proj`.`project_id` = `cb`.`project_id`))) join `users` `ub` on((`cb`.`user_id` = `ub`.`user_id`)))) ;

--
-- VIEW  `view_project_users`
-- Data: None
--

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
