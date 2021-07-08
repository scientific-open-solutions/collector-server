
CREATE TABLE `contributors` (
  `project_id` int(13) NOT NULL,
  `user_id` int(13) DEFAULT NULL,
  `contributor_status` varchar(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
