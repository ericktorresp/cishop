-- 
-- Table structure for table `<%value table_prefix%>wait` 
-- 

<%sec-start drop%>
DROP TABLE IF EXISTS `<%value table_prefix%>wait`;
<%sec-end drop%>

<%sec-start table%>
CREATE TABLE IF NOT EXISTS `<%value table_prefix%>wait` (
`client_uniq` varchar(34) NOT NULL,
`client_dtm` double(40,10) NOT NULL,
`client_ip` varchar(20) NOT NULL,
`client_nickname` varchar(50) NOT NULL,
`client_flag` smallint(5) NOT NULL,
`site_key` varchar(50) NOT NULL,
`server_uniq` varchar(34),
`server_dtm` double(40,10),
`server_userid` bigint(16),
`server_nickname` varchar(50),
`server_auto_link` smallint(5),
`client_info_set` smallint(5),
`client_info` text,
  PRIMARY KEY  (`client_uniq`),
  KEY `admin_wait` (`site_key`, `client_info_set`),
  KEY `admin_wait_2` (`site_key`, `client_dtm`, `client_info_set`),
  KEY `cs1` (`client_uniq`, `server_uniq`),
  KEY `cs2` (`client_uniq`, `client_info_set`)
) ENGINE=MyISAM;

ALTER TABLE `<%value table_prefix%>wait` ADD `chat_start_ts` BIGINT( 16 ) NOT NULL DEFAULT '0' AFTER `server_auto_link` ;
ALTER TABLE `<%value table_prefix%>wait` ADD `client_cookie` VARCHAR( 34 ) NOT NULL DEFAULT '' AFTER `chat_start_ts` ;
ALTER TABLE `<%value table_prefix%>wait` ADD `client_ip_country` VARCHAR( 2 ) NOT NULL DEFAULT '' AFTER `client_ip` ;

ALTER TABLE `<%value table_prefix%>wait` CHANGE `client_dtm` `client_dtm` DECIMAL( 40, 10 ) NOT NULL  ;
ALTER TABLE `<%value table_prefix%>wait` CHANGE `server_dtm` `server_dtm` DECIMAL( 40, 10 ) NULL DEFAULT NULL;

<%sec-end table%>


-- --------------------------------------------------------

