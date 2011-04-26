-- 
-- Table structure for table `<%value table_prefix%>visitors` 
-- 

<%sec-start drop%>
DROP TABLE IF EXISTS `<%value table_prefix%>visitors`;
<%sec-end drop%>

<%sec-start table%>


CREATE TABLE `<%value table_prefix%>visitors` (
`siteid` bigint(16) NOT NULL,
`ip` varchar(20) NOT NULL,
`dtm` double(40,10) NOT NULL,
`url` varchar(255) default NULL,
`tag` varchar(30) default NULL,
`browser` varchar(30) default NULL,
PRIMARY KEY  (`siteid`,`ip`),
KEY `siteid` (`siteid`,`dtm`)
) ENGINE=MyISAM;

ALTER TABLE `<%value table_prefix%>visitors` ADD `ip_country` varchar( 2 ) DEFAULT NULL AFTER `browser` ;
ALTER TABLE `<%value table_prefix%>visitors` ADD `ip_x` double(10,5) DEFAULT '0.00000' AFTER `ip_country` ;
ALTER TABLE `<%value table_prefix%>visitors` ADD `ip_y` double(10,5) DEFAULT '0.00000' AFTER `ip_x` ;
ALTER TABLE `<%value table_prefix%>visitors` ADD `caller_sid` varchar(50) DEFAULT NULL AFTER `ip_y` ;

ALTER TABLE `<%value table_prefix%>visitors` CHANGE `dtm` `dtm` DECIMAL( 40, 10 ) NOT NULL  ;


<%sec-end table%>


-- --------------------------------------------------------

