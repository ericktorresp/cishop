-- 
-- Table structure for table `<%value table_prefix%>session` 
-- 
<%sec-start drop%>
DROP TABLE IF EXISTS `<%value table_prefix%>session`;
<%sec-end drop%>


<%sec-start table%>
CREATE TABLE IF NOT EXISTS `<%value table_prefix%>session` (
`id` varchar(32) NOT NULL,
`last_act` bigint(16) NOT NULL,
`data` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `last_act` (`last_act`)
) ENGINE=MyISAM;

<%sec-end table%>

-- --------------------------------------------------------

