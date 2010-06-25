--
-- Table structure for table `ext_comment_comment`
--

CREATE TABLE `ext_comment_comment` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`date_create` int(10) unsigned NOT NULL DEFAULT '0',
	`date_update` int(10) unsigned NOT NULL DEFAULT '0',
	`deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
	`id_person_create` int(10) unsigned NOT NULL DEFAULT '0',
	`id_task` mediumint(9) unsigned NOT NULL DEFAULT '0',
	`comment` text NOT NULL,
	`is_public` tinyint(1) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `task` (`id_task`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ext_comment_feedback`
--

CREATE TABLE `ext_comment_feedback` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`date_create` int(10) unsigned NOT NULL,
	`date_update` int(10) unsigned NOT NULL,
	`id_person_create` int(10) unsigned NOT NULL,
	`id_person_feedback` int(10) unsigned NOT NULL,
	`id_comment` int(10) unsigned NOT NULL,
	`is_seen` tinyint(1) unsigned NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ext_comment_mailed`
--

CREATE TABLE `ext_comment_mailed` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`date_create` int(10) unsigned NOT NULL,
	`date_update` int(10) unsigned NOT NULL,
	`id_person_create` int(10) unsigned NOT NULL,
	`id_comment` int(10) unsigned NOT NULL,
	`id_person_mailed` int(10) unsigned NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
