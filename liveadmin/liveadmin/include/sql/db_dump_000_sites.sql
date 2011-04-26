--
-- Table structure for table `<%value table_prefix%>sites`
--

<%sec-start drop%>
DROP TABLE IF EXISTS `<%value table_prefix%>sites`;
<%sec-end drop%>

<%sec-start table%>
CREATE TABLE IF NOT EXISTS `<%value table_prefix%>sites` (
`siteid` bigint(16) NOT NULL,
`site_status` smallint(5) NOT NULL,
`serverid` bigint(16) NOT NULL,
`last_act` bigint(16) NOT NULL,
`in_trial` smallint(5) DEFAULT '1' NOT NULL,
`signup_date` bigint(16) NOT NULL,
`approve_date` bigint(16) NOT NULL,
`expiry_date` bigint(16) NOT NULL,
`company` varchar(60) NOT NULL,
`address` varchar(100) NOT NULL,
`city` varchar(30) NOT NULL,
`state` varchar(5) NOT NULL,
`country` varchar(5) NOT NULL,
`postal_code` varchar(15) NOT NULL,
`general_email` varchar(80) NOT NULL,
`no_answer_email` varchar(80) NOT NULL,
`theme` varchar(50) DEFAULT 'default' NOT NULL,
`language` varchar(50) DEFAULT 'english' NOT NULL,
`time_zone` smallint(5) DEFAULT '-300' NOT NULL,
`wait_find_rep_s` bigint(16) DEFAULT '60' NOT NULL,
`no_answer_act` smallint(5) NOT NULL,
`offline_act` smallint(5) NOT NULL,
`flash_install` smallint(5) DEFAULT '1' NOT NULL,
`sound_new_message` varchar(50) DEFAULT 'bell_clang.mp3',
`on_off_theme` varchar(10) DEFAULT 'tm_02',
`offline_image` text,
`online_image` text,
`chat_window_options` text,
`chat_window_texts` text,
`extra_fields` text,
  PRIMARY KEY  (`siteid`)
) ENGINE=MyISAM;

ALTER TABLE `<%value table_prefix%>sites` ADD `auto_invite` SMALLINT( 5 ) NOT NULL DEFAULT '0' AFTER `flash_install` ;
ALTER TABLE `<%value table_prefix%>sites` ADD `auto_invite_delay` SMALLINT( 5 ) NOT NULL DEFAULT '30' AFTER `auto_invite` ;

ALTER TABLE `<%value table_prefix%>sites` ADD `demo_mode` SMALLINT( 5 ) NOT NULL DEFAULT '0' AFTER `in_trial` ;

ALTER TABLE `<%value table_prefix%>sites` ADD `no_answer_email_type` SMALLINT( 5 ) NOT NULL DEFAULT '0' AFTER `no_answer_email` ;

ALTER TABLE `<%value table_prefix%>sites` ADD `refid` BIGINT( 16 ) NOT NULL DEFAULT '0' AFTER `expiry_date` ;

ALTER TABLE `<%value table_prefix%>sites` ADD `show_affiliate_link` SMALLINT( 5 ) NOT NULL DEFAULT '1' AFTER `refid` ;

ALTER TABLE `<%value table_prefix%>sites` ADD `signup_ip` varchar( 20 ) NOT NULL DEFAULT '' AFTER `show_affiliate_link` ;

ALTER TABLE `<%value table_prefix%>sites` ADD `enable_callback` SMALLINT( 5 ) NOT NULL DEFAULT '0' AFTER `signup_ip` ;

ALTER TABLE `<%value table_prefix%>sites` ADD `license` varchar( 250 ) DEFAULT NULL AFTER `auto_invite_delay` ;

<%sec-end table%>

-- --------------------------------------------------------

<%sec-start stl_data%>

INSERT INTO `<%value table_prefix%>sites` SET `siteid`='<%value siteid%>',
`site_status`='1',
`expiry_date`='0',
`approve_date`='1',
`signup_date`='<%value lv_signup_date%>',
`in_trial`='1',
`company`='<%value lv_company%>',
`signup_ip`='',
`refid`='0',
`general_email`='<%value lv_general_email%>',
`theme`='<%value lv_theme%>'

<%sec-end stl_data%>
