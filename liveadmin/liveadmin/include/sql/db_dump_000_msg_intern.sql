-- 
-- Table structure for table `<%value table_prefix%>msg_intern` 
-- 
<%sec-start drop%>
DROP TABLE IF EXISTS `<%value table_prefix%>msg_intern`;
<%sec-end drop%>


<%sec-start table%>

CREATE TABLE `<%value table_prefix%>msg_intern` (
`userid` bigint(16) NOT NULL,
`umsgid` bigint(16) NOT NULL auto_increment,
`status` smallint(5) NOT NULL default '0',
`dtm` double(40,10) NOT NULL,
`sender_userid` bigint(16) NOT NULL,
`message_1` varchar(255) default NULL,
`message_2` varchar(255) default NULL,
PRIMARY KEY  (`userid`,`umsgid`,`status`)
) ENGINE=MyISAM ;


ALTER TABLE `<%value table_prefix%>msg_intern` ADD `sender_nickname` VARCHAR( 50 ) NOT NULL DEFAULT '' AFTER `sender_userid` ;

ALTER TABLE `<%value table_prefix%>msg_intern` CHANGE `dtm` `dtm` DECIMAL( 40, 10 ) NOT NULL  ;



<%sec-end table%>

-- --------------------------------------------------------

