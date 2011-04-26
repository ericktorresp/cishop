--
-- Table structure for table `<%value table_prefix%>users`
--

<%sec-start drop%>
DROP TABLE IF EXISTS `<%value table_prefix%>users`;
<%sec-end drop%>

<%sec-start table%>
CREATE TABLE IF NOT EXISTS `<%value table_prefix%>users` (
`userid` bigint(16) NOT NULL,
`siteid` bigint(16) NOT NULL,
`access_level` bigint(16) NOT NULL,
`username` varchar(50) NOT NULL,
`password` varchar(50) NOT NULL,
`email` varchar(80) NOT NULL,
`ac_status` smallint(5) NOT NULL,
`firstname` varchar(50) NOT NULL,
`lastname` varchar(50) NOT NULL,
`nickname` varchar(50) NOT NULL,
`language` varchar(50) DEFAULT 'english' NOT NULL,
`time_zone` smallint(5) DEFAULT '-300' NOT NULL,
`sound_new_client` varchar(50) DEFAULT 'ringin.mp3' NOT NULL,
`sound_new_message` varchar(50) DEFAULT 'bell_clang.mp3' NOT NULL,
`send_init_1` varchar(100),
`send_init_2` varchar(100),
`send_init_3` varchar(100),
`auto_link` smallint(5) DEFAULT '1' NOT NULL,
`has_pic` smallint(5) DEFAULT '0' NOT NULL,
  PRIMARY KEY  (`userid`),
  KEY `email` (`email`),
  KEY `username` (`username`)
) ENGINE=MyISAM;

ALTER TABLE `<%value table_prefix%>users` ADD `last_uact` BIGINT( 16 ) NOT NULL DEFAULT '0' AFTER `ac_status` ;
ALTER TABLE `<%value table_prefix%>users` ADD `can_see_agents` SMALLINT( 5 ) NOT NULL DEFAULT '0' AFTER `last_uact` ;
ALTER TABLE `<%value table_prefix%>users` ADD `show_missed_calls` SMALLINT( 5 ) NOT NULL DEFAULT '0' AFTER `auto_link` ;
ALTER TABLE `<%value table_prefix%>users` ADD `check_msg_intern` BIGINT( 16 ) NOT NULL DEFAULT '0' AFTER `has_pic` ;
ALTER TABLE `<%value table_prefix%>users` ADD `can_see_visitors` SMALLINT( 5 ) NOT NULL DEFAULT '0' AFTER `can_see_agents` ;
ALTER TABLE `<%value table_prefix%>users` ADD `check_update` SMALLINT( 5 ) NOT NULL DEFAULT '0' AFTER `check_msg_intern` ;
ALTER TABLE `<%value table_prefix%>users` ADD `gflags` VARCHAR( 50 ) NOT NULL DEFAULT '' AFTER `check_update` ;


<%sec-end table%>



-- --------------------------------------------------------

<%sec-start stl_data%>

INSERT INTO `<%value table_prefix%>users` SET `userid`='<%value userid%>',
`siteid`='<%value siteid%>',
`ac_status`='1',
`access_level`='0',
`firstname`='<%value lv_firstname%>',
`nickname`='<%value lv_firstname%>',
`lastname`='<%value lv_lastname%>',
`username`='<%value lv_username%>',
`password`='<%value lv_password%>',
`email`='<%value lv_email%>',
`check_update`='1'

<%sec-end stl_data%>

