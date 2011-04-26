-- 
-- Table structure for table `<%value table_prefix%>msg_hist` 
-- 

<%sec-start drop%>
DROP TABLE IF EXISTS `<%value table_prefix%>msg_hist`;
<%sec-end drop%>

<%sec-start table%>
CREATE TABLE IF NOT EXISTS `<%value table_prefix%>msg_hist` (
`userid` bigint(16) NOT NULL,
`msg_md5` varchar(34) NOT NULL,
`edate` bigint(16) NOT NULL,
`message_tag` varchar(20),
`message` text,
  PRIMARY KEY  (`userid`, `msg_md5`),
  KEY `ms1` (`userid`, `edate`, `message_tag`)
) ENGINE=MyISAM;
<%sec-end table%>
-- --------------------------------------------------------

