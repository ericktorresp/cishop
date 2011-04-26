-- 
-- Table structure for table `<%value table_prefix%>queue` 
-- 

<%sec-start drop%>
DROP TABLE IF EXISTS `<%value table_prefix%>queue`;
<%sec-end drop%>

<%sec-start table%>
CREATE TABLE IF NOT EXISTS `<%value table_prefix%>queue` (
`dtm` double(40,10) NOT NULL,
`status` bigint(16) NOT NULL,
`direction` smallint(5) NOT NULL,
`serverid` bigint(16) NOT NULL,
`error_count` bigint(16) NOT NULL,
`sql_data` text NOT NULL,
  PRIMARY KEY  (`dtm`),
  KEY `status` (`status`)
) ENGINE=MyISAM;

ALTER TABLE `<%value table_prefix%>queue` CHANGE `dtm` `dtm` DECIMAL( 40, 10 ) NOT NULL  ;

<%sec-end table%>

-- --------------------------------------------------------

