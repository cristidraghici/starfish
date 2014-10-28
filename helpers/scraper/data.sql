-- Dumping database structure for parser
CREATE DATABASE IF NOT EXISTS `parser` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_bin */;
USE `parser`;


-- Dumping structure for table parser.projects
CREATE TABLE IF NOT EXISTS `projects` (
  `nr_crt` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` text COLLATE utf8_bin,
  PRIMARY KEY (`nr_crt`),
  UNIQUE KEY `nr_crt` (`nr_crt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table parser.urls
CREATE TABLE IF NOT EXISTS `urls` (
  `nr_crt` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(5000) COLLATE utf8_bin NOT NULL,
  `method` varchar(50) COLLATE utf8_bin NOT NULL,
  `parameters` varchar(5000) COLLATE utf8_bin NOT NULL,
  `data` varchar(5000) COLLATE utf8_bin NOT NULL,
  `storage` longtext COLLATE utf8_bin NOT NULL,
  `options` longtext COLLATE utf8_bin NOT NULL,
  `project_id` smallint(5) unsigned DEFAULT NULL,
  `group_id` smallint(5) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  `status_download` tinyint(1) unsigned NOT NULL,
  `status_process` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`nr_crt`),
  UNIQUE KEY `nr_crt` (`nr_crt`),
  KEY `url_method_parameters_data` (`url`(255),`method`,`parameters`(255),`data`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='* storage\r\nstores additional information about the parsed file\r\n\r\n* type\r\n1 - downloaded at every run of the parser\r\n2 - permanent\r\n\r\n* status_download\r\n1 - waiting\r\n2 - in use\r\n3 - done\r\n4 - failed\r\n\r\n* status_process\r\n1 - waiting\r\n2 - done';

-- Data exporting was unselected.


-- Dumping structure for table parser.url_download
CREATE TABLE IF NOT EXISTS `url_download` (
  `url_id` bigint(20) unsigned NOT NULL,
  `content` longblob NOT NULL,
  PRIMARY KEY (`url_id`),
  UNIQUE KEY `url_id` (`url_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table parser.url_processed
CREATE TABLE IF NOT EXISTS `url_processed` (
  `url_id` bigint(20) unsigned NOT NULL,
  `content` longtext COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`url_id`),
  UNIQUE KEY `url_id` (`url_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.
