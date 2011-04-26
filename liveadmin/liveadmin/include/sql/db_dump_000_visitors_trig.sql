-- 
-- Table structure for table `<%value table_prefix%>visitors_trig` 
-- 

<%sec-start drop%>
DROP TABLE IF EXISTS `<%value table_prefix%>visitors_trig`;
<%sec-end drop%>

<%sec-start table%>


CREATE TABLE `<%value table_prefix%>visitors_trig` (
`id` bigint(16) NOT NULL auto_increment,
`dtm` double(40,20) NOT NULL,
`ip` varchar(20) NOT NULL,
PRIMARY KEY  (`id`),
KEY `ip` (`ip`)
) ENGINE=MyISAM;

ALTER TABLE `<%value table_prefix%>visitors_trig` CHANGE `dtm` `dtm` DECIMAL( 40, 20 ) NOT NULL  ;

<%sec-end table%>


-- --------------------------------------------------------

