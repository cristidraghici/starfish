-- --------------------------------------------------------
-- Host:                         attitude.ml
-- Server version:               5.6.19-0ubuntu0.14.04.1 - (Ubuntu)
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Version:             8.3.0.4694
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

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


-- Dumping structure for function parser._project_get_id
DELIMITER //
CREATE DEFINER=`%`@`%` FUNCTION `_project_get_id`(`$title` TEXT) RETURNS bigint(20)
    READS SQL DATA
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN

declare _title_exists bigint;

select count(*) into _title_exists from projects where title=$title;

if _title_exists=0 then
	insert into projects(title) values($title);
end if;

select nr_crt into _title_exists from projects where title=$title;

return _title_exists;

END//
DELIMITER ;


-- Dumping structure for procedure parser._status
DELIMITER //
CREATE DEFINER=`%`@`%` PROCEDURE `_status`(IN `$project_id` INT, INOUT `total` INT)
    SQL SECURITY INVOKER
BEGIN

END//
DELIMITER ;


-- Dumping structure for function parser._url_add
DELIMITER //
CREATE DEFINER=`%`@`%` FUNCTION `_url_add`(`$project_id` BIGINT, `$group_id` BIGINT, `$type` INT, `$url` VARCHAR(500), `$method` VARCHAR(50), `$parameters` LONGTEXT, `$data` LONGTEXT, `$storage` LONGTEXT, `$options` LONGTEXT) RETURNS bigint(20)
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN

declare _url_exists bigint;
select count(*) into _url_exists from urls where url=$url and method=$method and parameters=$parameters and `data`=$data;

if _url_exists=0 then
	insert into urls(`url`, `parameters`, `data`, `method`, `project_id`, `group_id`, `storage`, `options`, `type`, `status_download`, `status_process`) values($url, $parameters, $data, $method, $project_id, $group_id, $storage, $options, $type, 1, 1);
else
	update urls set `project_id`=$project_id, `group_id`=$group_id, `storage`=$storage, `options`=$options, `type`=$type, `status_download`=1, `status_process`=1 where url=$url and method=$method and parameters=$parameters and `data`=$data;
end if;

return 1;
END//
DELIMITER ;


-- Dumping structure for function parser._url_set_download
DELIMITER //
CREATE DEFINER=`%`@`%` FUNCTION `_url_set_download`(`$url_id` BIGINT, `$status` BIGINT) RETURNS tinyint(4)
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN

update urls set status_download=$status where nr_crt=$url_id;

RETURN 1;

END//
DELIMITER ;


-- Dumping structure for function parser._url_set_process
DELIMITER //
CREATE DEFINER=`%`@`%` FUNCTION `_url_set_process`(`$url_id` INT, `$status` INT) RETURNS int(11)
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN

update urls set status_process=$status where nr_crt=$url_id;

RETURN 1;
END//
DELIMITER ;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
